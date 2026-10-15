<?php

namespace App\Console\Commands;

use App\Enums\ProtectedFileSet;
use App\ProtectedFile;
use App\Services\ProtectedFiles\LegacyMigration;
use App\Services\ProtectedFiles\ProtectedFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

/**
 * Rollback of the cutover: makes the previous release work again.
 *
 *  - every quarantined public file (_retired/, _orphans/) goes back where it was;
 *  - files uploaded since the cutover exist only privately, so each is COPIED to
 *    the public path the old code expects (the private copy and its record stay).
 *
 * The owning tables were never changed, so after this the old code finds every
 * file at the path its columns hold. Nothing is deleted.
 */
class ProtectFilesRestore extends Command
{
    protected $signature = 'files:protect:restore {--dry-run : Show what would be restored without touching anything}';

    protected $description = 'Rollback: put retired public copies back and re-publish files uploaded since the cutover';

    public function handle(LegacyMigration $migration, ProtectedFiles $files): int
    {
        $dry = (bool) $this->option('dry-run');
        $root = rtrim(config('protected_files.root'), '/');
        $back = $orphansBack = $republished = $skipped = 0;

        // 1. quarantined originals: each record knows the URL path its file was retired under
        foreach (ProtectedFile::withTrashed()->whereNotNull('legacy_path')->cursor() as $record) {
            $parked = $migration->retiredPath($record->legacy_path);
            if (!is_file($parked)) {
                continue;
            }
            $original = $files->normalize($record->set, $record->legacy_path);
            if ($original === null) {
                $skipped++;
                continue;
            }
            ($dry || $migration->move($parked, $files->legacyPath($record->set, $original))) ? $back++ : $skipped++;
        }
        // 2. parked orphans
        foreach (ProtectedFileSet::all() as $set) {
            $dir = $root . '/_orphans/' . $set;
            if (!is_dir($dir)) {
                continue;
            }
            foreach ((new Finder())->files()->in($dir)->ignoreDotFiles(false) as $file) {
                $relative = ltrim(substr($file->getPathname(), strlen($dir)), '/');
                ($dry || $migration->move($file->getPathname(), $files->legacyPath($set, $relative))) ? $orphansBack++ : $skipped++;
            }
        }
        // 3. files that only exist privately (uploaded after the cutover)
        foreach (ProtectedFile::whereIn('set', ProtectedFileSet::all())->cursor() as $record) {
            if ($record->disk !== $files->disk($record->set)) {
                continue;
            }
            $public = $files->legacyPath($record->set, $record->path);
            $private = Storage::disk($record->disk)->path($record->path);
            if (is_file($public) || !is_file($private)) {
                continue;
            }
            if (!$dry) {
                if (!is_dir(dirname($public))) {
                    mkdir(dirname($public), 0775, true);
                }
                copy($private, $public);
            }
            $republished++;
        }

        $this->table(['Retired files put back', 'Orphans put back', 'Post-cutover uploads re-published', 'Skipped (target exists)'], [[$back, $orphansBack, $republished, $skipped]]);
        $this->info($dry ? 'Dry run: nothing was changed.' : 'Done. Set PROTECTED_FILES_LEGACY_FALLBACK=true and deploy the previous release.');

        return self::SUCCESS;
    }
}
