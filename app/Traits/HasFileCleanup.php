<?php

namespace App\Traits;

use App\Services\FileManagerService;

trait HasFileCleanup
{
    /**
     * Boot the trait and register model event listeners.
     */
    public static function bootHasFileCleanup()
    {
        static::deleted(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }

            $model->deleteAssociatedFiles();
        });
    }

    /**
     * Delete all files associated with the model's file fields.
     */
    public function deleteAssociatedFiles()
    {
        if (!property_exists($this, 'fileFields')) {
            return;
        }

        $fileManager = app(FileManagerService::class);

        foreach ($this->fileFields as $field => $disk) {
            $value = $this->getRawOriginal($field);
            if (!$value) {
                continue;
            }

            if ($value === '/images/background/logo.png' || $value === '/images/placeholder.jpg') {
                continue;
            }

            if ($this->isJsonValue($value)) {
                $paths = json_decode($value, true);
                if (is_array($paths)) {
                    foreach ($paths as $path) {
                        $fileManager->delete($path, $disk);
                    }
                }
            } else {
                $fileManager->delete($value, $disk);
            }
        }
    }

    /**
     * Check if a string is valid JSON.
     */
    protected function isJsonValue(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
