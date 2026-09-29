<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FileManagerService;

class MigrateFileUrlsToPaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:migrate-to-paths 
                            {--model= : The Eloquent model class name, e.g., "App\User"} 
                            {--column=* : The column(s) to migrate from full URLs to relative paths} 
                            {--disk= : Optional disk name to use for relative path parsing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate database columns containing full URLs to relative file paths';

    protected FileManagerService $fileManager;

    /**
     * Create a new command instance.
     */
    public function __construct(FileManagerService $fileManager)
    {
        parent::__construct();
        $this->fileManager = $fileManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelClass = $this->option('model');
        $columns = $this->option('column');
        $disk = $this->option('disk');

        if (!$modelClass) {
            $this->error('Please specify a model class using the --model option.');
            return 1;
        }

        if (empty($columns)) {
            $this->error('Please specify at least one column using the --column option.');
            return 1;
        }

        if (!class_exists($modelClass)) {
            $this->error("Model class '{$modelClass}' does not exist.");
            return 1;
        }

        $this->info("Starting migration for Model: {$modelClass} on columns: " . implode(', ', $columns));

        $count = 0;
        $updatedCount = 0;

        $modelClass::chunkById(100, function ($records) use ($columns, $disk, &$count, &$updatedCount) {
            foreach ($records as $record) {
                $count++;
                $updated = false;

                foreach ($columns as $column) {
                    $originalValue = $record->getRawOriginal($column);

                    if (!$originalValue) {
                        continue;
                    }

                    // If value is a JSON array (multiple files)
                    if ($this->isJson($originalValue)) {
                        $paths = json_decode($originalValue, true);
                        if (is_array($paths)) {
                            $newPaths = array_map(fn($path) => $this->fileManager->getRelativePath($path, $disk), $paths);
                            $newValue = json_encode($newPaths);
                            if ($originalValue !== $newValue) {
                                $record->$column = $newPaths; // Set as array, Eloquent will cast/serialize it
                                $updated = true;
                            }
                        }
                    } else {
                        $newPath = $this->fileManager->getRelativePath($originalValue, $disk);
                        if ($originalValue !== $newPath) {
                            $record->$column = $newPath;
                            $updated = true;
                        }
                    }
                }

                if ($updated) {
                    $record->save();
                    $updatedCount++;
                }
            }
        });

        $this->info("Completed! Processed {$count} records. Updated {$updatedCount} records.");
        return 0;
    }

    /**
     * Check if a string is valid JSON.
     */
    protected function isJson(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
