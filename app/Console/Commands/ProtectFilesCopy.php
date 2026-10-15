<?php

namespace App\Console\Commands;

use App\Enums\ProtectedFileSet;
use App\Services\ProtectedFiles\LegacyMigration;
use Illuminate\Console\Command;

/**
 * Step 2 of making confidential uploads private: copy every referenced file to
 * its private disk, check the copy byte for byte, and record it.
 *
 * The old public file is NOT touched, so this can run while the site is live and
 * can be repeated as often as needed (already copied files are skipped).
 * Run it as the web server's user so the copies are readable and writable by the app.
 */
class ProtectFilesCopy extends Command
{
    protected $signature = 'files:protect:copy
        {--set=* : Limit to these sets (default: all)}
        {--dry-run : Show what would be copied without copying}';

    protected $description = 'Copy confidential uploads to the private disks and record them (old files stay in place)';

    public function handle(LegacyMigration $migration): int
    {
        $dry = (bool) $this->option('dry-run');
        $rows = [];
        $failed = 0;

        foreach ($this->option('set') ?: ProtectedFileSet::all() as $set) {
            $count = array_fill_keys([LegacyMigration::COPY, LegacyMigration::DONE, LegacyMigration::MISSING, LegacyMigration::NOT_A_FILE, LegacyMigration::SHARED, LegacyMigration::FOREIGN, LegacyMigration::OVERWRITTEN], 0);
            $copied = 0;

            foreach ($migration->plan($set) as $item) {
                $count[$item['state']]++;
                if ($item['state'] !== LegacyMigration::COPY || $dry) {
                    if (in_array($item['state'], [LegacyMigration::SHARED, LegacyMigration::FOREIGN], true)) {
                        $this->warn("[{$set}] needs a decision ({$item['state']}): {$item['owner_type']} #{$item['owner_id']} {$item['field']} -> {$item['path']}");
                    }
                    continue;
                }
                try {
                    $migration->copy($set, $item);
                    $copied++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error("[{$set}] {$item['path']}: " . $e->getMessage());
                }
            }

            $rows[] = [$set, $count[LegacyMigration::COPY], $dry ? '-' : $copied, $count[LegacyMigration::DONE], $count[LegacyMigration::MISSING], $count[LegacyMigration::NOT_A_FILE], $count[LegacyMigration::OVERWRITTEN], $count[LegacyMigration::SHARED] + $count[LegacyMigration::FOREIGN]];
        }

        $this->table(['Set', 'To copy', 'Copied now', 'Already private', 'File missing', 'Not a file', 'Overwritten earlier', 'Needs a decision'], $rows);
        $this->info($dry ? 'Dry run: nothing was copied.' : 'Old public files were left in place.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
