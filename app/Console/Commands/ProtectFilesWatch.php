<?php

namespace App\Console\Commands;

use App\Enums\ProtectedFileSet;
use App\Services\ProtectedFiles\LegacyReferences;
use App\Services\ProtectedFiles\ProtectedFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;

/**
 * After the cutover the old web-served upload folders must stay empty. A file
 * appearing there means some code path still writes confidential uploads to a
 * public location. This reports it (and logs a warning) so it is noticed the
 * next day instead of at the next audit. Read-only; quiet before the cutover.
 */
class ProtectFilesWatch extends Command
{
    protected $signature = 'files:protect:watch';

    protected $description = 'Read-only: warn if confidential uploads reappear in the old public folders after the cutover';

    public function handle(ProtectedFiles $files, LegacyReferences $references): int
    {
        if (config('protected_files.legacy_fallback')) {
            $this->line('Cutover not done yet (legacy fallback is on): nothing to watch.');

            return self::SUCCESS;
        }

        $found = [];
        foreach (ProtectedFileSet::all() as $set) {
            foreach ($references->legacyAreas($set) as $area) {
                $base = rtrim($files->legacyPath($set, ''), '/');
                $dir = $area['dir'] === '' ? $base : $base . '/' . $area['dir'];
                if (!is_dir($dir)) {
                    continue;
                }
                foreach ((new Finder())->files()->in($dir)->ignoreDotFiles(true) as $file) {
                    if (isset($area['only']) && !in_array(strtolower($file->getExtension()), $area['only'], true)) {
                        continue;
                    }
                    $found[$file->getPathname()] = $set;
                }
            }
        }

        if (!$found) {
            $this->info('The old public upload folders are empty.');

            return self::SUCCESS;
        }

        Log::warning('Confidential uploads found in a web-served folder after the cutover', ['count' => count($found), 'examples' => array_slice(array_keys($found), 0, 10)]);
        $this->error(count($found) . ' file(s) in the old public upload folders:');
        foreach (array_slice(array_keys($found), 0, 20) as $path) {
            $this->line('  ' . $path);
        }

        return self::FAILURE;
    }
}
