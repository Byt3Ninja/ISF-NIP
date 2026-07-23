<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigratePortfolioFiles extends Command
{
    protected $signature = 'portfolio:migrate-storage';
    protected $description = 'Migrate portfolio files from public/191014 to Laravel storage';

    public function handle()
    {
        // Ensure target directory exists with proper permissions
        $targetDir = storage_path('app/public/service_providers_portfolio');
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0775, true);
        }

        $sourcePath = public_path('191014');
        $portfolios = DB::table('service_provider_portfolios')
            ->whereNotNull('media_url')
            ->where('media_url', '!=', '')
            ->where('media_url', '!=', '0')
            ->get();

        if ($portfolios->isEmpty()) {
            $this->info('No portfolio records found.');
            return 0;
        }

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($portfolios as $portfolio) {
            $relativePath = $portfolio->media_url;

            // Already in storage
            if (Storage::disk('public')->exists($relativePath)) {
                $this->line("  SKIP (already in storage): {$relativePath}");
                $skipped++;
                continue;
            }

            $oldFile = $sourcePath . '/' . ltrim($relativePath, '/');

            if (!File::exists($oldFile)) {
                $this->warn("  MISSING: {$oldFile}");
                $failed++;
                continue;
            }

            // Copy to storage (preserves original for safe rollback)
            $contents = File::get($oldFile);
            Storage::disk('public')->put($relativePath, $contents);

            if (Storage::disk('public')->exists($relativePath)) {
                $this->info("  MIGRATED: {$relativePath}");
                $migrated++;
            } else {
                $this->error("  FAILED: {$relativePath}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Migrated: {$migrated}, Skipped: {$skipped}, Failed: {$failed}");
        $this->line('Original files in public/191014 have been preserved. Remove them manually after verifying.');

        return 0;
    }
}
