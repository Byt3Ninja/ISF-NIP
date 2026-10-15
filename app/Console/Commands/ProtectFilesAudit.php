<?php

namespace App\Console\Commands;

use App\Enums\ProtectedFileSet;
use App\ProtectedFile;
use App\Services\ProtectedFiles\LegacyReferences;
use App\Services\ProtectedFiles\ProtectedFiles;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Read-only survey of confidential uploads before they are made private.
 *
 * For every database reference it reports whether the value is a file path at
 * all, whether the file is on disk where the app expects it, and whether several
 * owners point at the same file; and it lists files on disk no row refers to.
 * The result is the baseline the migration is later verified against, so links
 * that are already broken today are not blamed on the move.
 *
 * It writes nothing: no file is touched, no row is changed.
 */
class ProtectFilesAudit extends Command
{
    protected $signature = 'files:protect:audit
        {--set=* : Limit to these sets (default: all)}
        {--json= : Also write the full findings to this file}
        {--show=10 : How many examples to print per problem}';

    protected $description = 'Read-only: survey confidential uploads (resolves / missing / not a file / shared / orphaned) before migrating them';

    public function handle(ProtectedFiles $files, LegacyReferences $references): int
    {
        $sets = $this->option('set') ?: ProtectedFileSet::all();
        $show = (int) $this->option('show');
        $report = [];
        $rows = [];

        foreach ($sets as $set) {
            $found = ['references' => 0, 'not_a_file' => [], 'resolves' => 0, 'missing' => [], 'shared' => [], 'recorded' => 0, 'orphans' => [], 'orphan_bytes' => 0];
            $owners = [];   // normalized path => distinct owners
            $referenced = [];

            foreach ($references->each($set) as $reference) {
                $found['references']++;
                $path = $files->normalize($set, $reference['value']);

                if ($path === null) {
                    $found['not_a_file'][] = $this->describe($reference);
                    continue;
                }

                $referenced[$path] = true;
                $owners[$path][$reference['owner_type'] . ':' . $reference['owner_id'] . ':' . ($reference['company_id'] ?? '-')] = true;

                if (is_file($files->legacyPath($set, $path))) {
                    $found['resolves']++;
                } elseif (ProtectedFile::where('set', $set)->where('path', $path)->exists()) {
                    $found['recorded']++;
                } else {
                    $found['missing'][] = $this->describe($reference) . ' -> ' . $path;
                }
            }

            foreach ($owners as $path => $who) {
                if (count($who) > 1) {
                    $found['shared'][] = $path . '  <- ' . implode(', ', array_keys($who)) . '  (the latest writer keeps it, if the rows are dated)';
                }
            }

            foreach ($references->legacyAreas($set) as $area) {
                $base = rtrim($files->legacyPath($set, ''), '/');
                $dir = $area['dir'] === '' ? $base : $base . '/' . $area['dir'];
                if (!is_dir($dir)) {
                    continue;
                }
                foreach ((new Finder())->files()->in($dir)->ignoreDotFiles(true) as $file) {
                    $relative = ltrim(substr($file->getPathname(), strlen($base)), '/');
                    if (isset($area['only']) && !in_array(strtolower($file->getExtension()), $area['only'], true)) {
                        continue;
                    }
                    if (!isset($referenced[$relative])) {
                        $found['orphans'][] = $relative;
                        $found['orphan_bytes'] += $file->getSize();
                    }
                }
            }

            $report[$set] = $found;
            $rows[] = [
                $set,
                $found['references'],
                $found['resolves'],
                $found['recorded'],
                count($found['missing']),
                count($found['not_a_file']),
                count($found['shared']),
                count($found['orphans']) . ' (' . $this->size($found['orphan_bytes']) . ')',
            ];
        }

        $this->table(['Set', 'References', 'File found', 'Already private', 'File missing', 'Not a file path', 'Shared path', 'Orphan files'], $rows);

        foreach ($report as $set => $found) {
            foreach (['missing' => 'File missing (link is already broken)', 'not_a_file' => 'Value is not a file path (left alone)', 'shared' => 'Same file referenced by more than one owner (needs a human decision)', 'orphans' => 'On disk but referenced by no row (will be quarantined, not served)'] as $key => $title) {
                if ($found[$key] && $show > 0) {
                    $this->line('');
                    $this->line("<comment>[{$set}] {$title}: " . count($found[$key]) . '</comment>');
                    foreach (array_slice($found[$key], 0, $show) as $line) {
                        $this->line('  ' . $line);
                    }
                    if (count($found[$key]) > $show) {
                        $this->line('  … ' . (count($found[$key]) - $show) . ' more (use --json for the full list)');
                    }
                }
            }
        }

        if ($path = $this->option('json')) {
            file_put_contents($path, json_encode(['generated_at' => now()->toIso8601String(), 'sets' => $report], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->line('');
            $this->info("Full findings written to {$path}");
        }

        $this->line('');
        $this->info('Nothing was changed.');

        return self::SUCCESS;
    }

    protected function describe(array $reference): string
    {
        $value = (string) $reference['value'];

        return sprintf('%s #%d %s = %s', $reference['owner_type'], $reference['owner_id'], $reference['field'], mb_strlen($value) > 60 ? mb_substr($value, 0, 57) . '…' : $value);
    }

    protected function size(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1) . ' MB' : round($bytes / 1024) . ' KB';
    }
}
