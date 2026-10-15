<?php

namespace App\Console\Commands;

use App\Enums\ProtectedFileSet;
use App\ProtectedFile;
use App\Services\ProtectedFiles\LegacyMigration;
use App\Services\ProtectedFiles\LegacyReferences;
use App\Services\ProtectedFiles\ProtectedFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

/**
 * Step 4 (cutover): take the old public copies out of the web-served folders.
 *
 * Nothing is deleted. A public file is moved into {private root}/_retired/ only
 * if its private copy exists and is byte-identical; files no row refers to are
 * moved into {private root}/_orphans/. `files:protect:restore` puts them back.
 * Refuses to run unless `files:protect:verify` passes.
 */
class ProtectFilesRetire extends Command
{
    protected $signature = 'files:protect:retire
        {--set=* : Limit to these sets (default: all)}
        {--accept-undecided : Passed on to the verification}
        {--dry-run : Show what would be moved without moving}';

    protected $description = 'Move the old public copies of confidential uploads into a private quarantine (nothing is deleted)';

    public function handle(LegacyMigration $migration, ProtectedFiles $files, LegacyReferences $references): int
    {
        $sets = $this->option('set') ?: ProtectedFileSet::all();
        $dry = (bool) $this->option('dry-run');

        if (Artisan::call('files:protect:verify', ['--set' => $sets, '--accept-undecided' => (bool) $this->option('accept-undecided')]) !== self::SUCCESS) {
            $this->line(Artisan::output());
            $this->error('Not retiring anything: verification did not pass.');

            return self::FAILURE;
        }

        $rows = [];
        foreach ($sets as $set) {
            $retired = $kept = $orphans = 0;
            $accounted = [];

            foreach (ProtectedFile::where('set', $set)->where('disk', $files->disk($set))->cursor() as $record) {
                $old = $files->legacyPath($set, $record->path);
                $accounted[$record->path] = true;
                if (!is_file($old)) {
                    continue;
                }
                $private = Storage::disk($record->disk)->path($record->path);
                if (!is_file($private) || hash_file('sha256', $private) !== hash_file('sha256', $old)) {
                    $kept++;
                    $this->warn("[{$set}] left in place (private copy differs): {$record->path}");
                    continue;
                }
                if ($dry || $migration->move($old, $migration->retiredPath($migration->legacyUrlPath($set, $record->path)))) {
                    $retired++;
                } else {
                    $kept++;
                    $this->warn("[{$set}] could not be moved: {$old}");
                }
            }

            // files in this set's old folders that no record accounts for
            foreach ($references->legacyAreas($set) as $area) {
                $base = rtrim($files->legacyPath($set, ''), '/');
                $dir = $area['dir'] === '' ? $base : $base . '/' . $area['dir'];
                if (!is_dir($dir)) {
                    continue;
                }
                foreach ((new Finder())->files()->in($dir)->ignoreDotFiles(true) as $file) {
                    $relative = ltrim(substr($file->getPathname(), strlen($base)), '/');
                    if (isset($accounted[$relative]) || (isset($area['only']) && !in_array(strtolower($file->getExtension()), $area['only'], true))) {
                        continue;
                    }
                    if ($dry || $migration->move($file->getPathname(), $migration->orphanPath($set, $relative))) {
                        $orphans++;
                    }
                }
            }

            $rows[] = [$set, $retired, $orphans, $kept];
        }

        $this->table(['Set', $dry ? 'Would retire' : 'Retired', $dry ? 'Orphans to park' : 'Orphans parked', 'Left in place'], $rows);
        $this->info($dry
            ? 'Dry run: nothing was moved.'
            : 'Done. Set PROTECTED_FILES_LEGACY_FALLBACK=false, rebuild the config cache, and keep _retired/ until you are sure.');

        return self::SUCCESS;
    }
}
