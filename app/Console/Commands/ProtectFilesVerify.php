<?php

namespace App\Console\Commands;

use App\Enums\ProtectedFileSet;
use App\ProtectedFile;
use App\Services\ProtectedFiles\LegacyMigration;
use App\Services\ProtectedFiles\ProtectedFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Step 3: prove the copy is complete before any old file is retired.
 *
 * Passes only if every reference that points at an existing file is recorded,
 * its private copy exists, matches the recorded checksum and (where the old
 * public file is still there) is byte-identical to it. References that were
 * already broken before the migration are listed, not counted as failures.
 * Read-only.
 */
class ProtectFilesVerify extends Command
{
    protected $signature = 'files:protect:verify
        {--set=* : Limit to these sets (default: all)}
        {--accept-undecided : Pass even if some files are referenced by several owners with no way to tell whose they are (they get no link)}';

    protected $description = 'Read-only: check that every confidential file is recorded and intact on its private disk';

    public function handle(LegacyMigration $migration, ProtectedFiles $files): int
    {
        $rows = [];
        $problems = 0;

        foreach ($this->option('set') ?: ProtectedFileSet::all() as $set) {
            $ok = $pending = $broken = $undecided = $missingBefore = 0;

            foreach ($migration->plan($set) as $item) {
                switch ($item['state']) {
                    case LegacyMigration::NOT_A_FILE:
                    case LegacyMigration::OVERWRITTEN: // that row's own file was replaced on disk long ago
                        continue 2;
                    case LegacyMigration::MISSING:
                        $missingBefore++;
                        continue 2;
                    case LegacyMigration::COPY:
                        $pending++;
                        $this->warn("[{$set}] not copied yet: {$item['path']}");
                        continue 2;
                    case LegacyMigration::SHARED:
                    case LegacyMigration::FOREIGN:
                        $undecided++;
                        $this->warn("[{$set}] needs a decision ({$item['state']}): {$item['owner_type']} #{$item['owner_id']} -> {$item['path']}");
                        continue 2;
                }

                $record = ProtectedFile::withTrashed()->where('disk', $files->disk($set))->where('path', $item['path'])->first();
                if ($record->trashed()) {
                    $ok++; // retired on purpose after the reference was read; nothing to verify
                    continue;
                }
                $private = Storage::disk($record->disk)->path($record->path);
                $hash = is_file($private) ? hash_file('sha256', $private) : null;
                $sameAsOld = !is_file($item['source']) || hash_file('sha256', $item['source']) === $hash;

                if ($hash === null || $hash !== $record->sha256 || !$sameAsOld) {
                    $broken++;
                    $this->error("[{$set}] private copy is missing or differs: {$item['path']}");
                } else {
                    $ok++;
                }
            }

            $problems += $pending + $broken + ($this->option('accept-undecided') ? 0 : $undecided);
            $rows[] = [$set, $ok, $pending, $broken, $undecided, $missingBefore];
        }

        $this->table(['Set', 'Verified', 'Not copied yet', 'Missing or different', 'Needs a decision', 'Was already missing'], $rows);

        if ($problems) {
            $this->error('Verification failed: do NOT retire the old files.');

            return self::FAILURE;
        }
        $this->info('Every existing file is recorded and intact on its private disk.');

        return self::SUCCESS;
    }
}
