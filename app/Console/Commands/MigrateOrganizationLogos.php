<?php

namespace App\Console\Commands;

use App\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateOrganizationLogos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizations:migrate-logos {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate organization logos from public folder to storage disk';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $disk = Storage::disk('organizations');

        if ($dryRun) {
            $this->info('Running in dry-run mode. No changes will be made.');
        }

        // Get all organizations with logos in the old public path
        $organizations = Organization::whereNotNull('img_url')
            ->where('img_url', 'like', 'organizations/%')
            ->get();

        if ($organizations->isEmpty()) {
            $this->info('No organizations found with logos in the old public path.');
            return 0;
        }

        $this->info("Found {$organizations->count()} organizations with logos to migrate.");

        $migrated = 0;
        $failed = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($organizations->count());
        $bar->start();

        foreach ($organizations as $organization) {
            $oldPath = $organization->img_url;
            $publicPath = public_path($oldPath);

            // Check if file exists in public folder
            if (!File::exists($publicPath)) {
                $this->newLine();
                $this->warn("File not found: {$publicPath} (Organization ID: {$organization->id})");
                $skipped++;
                $bar->advance();
                continue;
            }

            // Generate new filename
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
            $newFilename = $organization->id . '_' . time() . '.' . $extension;

            if (!$dryRun) {
                try {
                    // Read file content
                    $content = File::get($publicPath);

                    // Store in new location
                    $disk->put($newFilename, $content);

                    // Update organization record
                    $organization->img_url = $newFilename;
                    $organization->save();

                    // Delete old file
                    File::delete($publicPath);

                    $migrated++;
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("Failed to migrate logo for Organization ID {$organization->id}: {$e->getMessage()}");
                    $failed++;
                }
            } else {
                $this->newLine();
                $this->line("Would migrate: {$oldPath} -> {$newFilename}");
                $migrated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migration complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Migrated', $migrated],
                ['Failed', $failed],
                ['Skipped (file not found)', $skipped],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('This was a dry run. Run without --dry-run to apply changes.');
        }

        return 0;
    }
}
