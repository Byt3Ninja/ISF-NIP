<?php

namespace App\Services;

use App\Formdata;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\FileAccessLog;
use App\Enums\ProtectedFileSet;
use App\Services\ProtectedFiles\ProtectedFiles;
use App\Services\ProtectedFiles\FileAccess;

class AdvancedExportService
{
    protected $storageDisk = 'local'; // Or 'public' depending on config
    protected $basePath = 'exports/form_submissions';

    public function getExportStatus($organizationId)
    {
        $path = $this->getExportPath($organizationId);
        $metaPath = $path . '/metadata.json';

        $totalEntries = $this->getTotalEntries($organizationId);

        $status = [
            'total_entries' => $totalEntries,
            'ready_to_download' => 0,
            'last_generated' => null,
            'file_name' => null,
            'file_size' => null,
            'has_export' => false
        ];

        if (Storage::disk($this->storageDisk)->exists($metaPath)) {
            $metadata = json_decode(Storage::disk($this->storageDisk)->get($metaPath), true);
            $status['ready_to_download'] = $metadata['count'] ?? 0;
            $status['last_generated'] = $metadata['timestamp'] ?? null;
            $status['file_name'] = $metadata['filename'] ?? null;

            // Check if file actually exists
            if (Storage::disk($this->storageDisk)->exists($path . '/' . $status['file_name'])) {
                $status['has_export'] = true;
                $status['file_size'] = $this->formatSize(Storage::disk($this->storageDisk)->size($path . '/' . $status['file_name']));
            }
        }

        return $status;
    }

    public function generateExport($organizationId)
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $tempDir = storage_path('app/temp/export_' . $organizationId . '_' . $timestamp);

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Fetch Data
        $request = new Request();
        if ($organizationId) {

        }



        $submissions = Formdata::formsData($request);
        $totalSubmissions = $submissions->count();

        $count = 0;
        $cacheKey = 'export_progress_' . ($organizationId ?? 'super_admin');
        Cache::put($cacheKey, 0, 600); // Initialize progress

        foreach ($submissions as $submission) {
            $startupName = $this->sanitizeFileName($submission->startup_name ?? 'Unknown Startup');
            $startupDir = $tempDir . '/' . $startupName;

            if (!File::exists($startupDir)) {
                File::makeDirectory($startupDir, 0755, true);
            }

            // Create Data File
            $content = "Submission ID: {$submission->submission_id}\n";
            $content .= "Startup Name: {$submission->startup_name}\n";
            $content .= "Submitted By: {$submission->submitted_by}\n";
            $content .= "Program: {$submission->program_name}\n";
            $content .= "Step: {$submission->step_name}\n";
            $content .= "Date: {$submission->created_at}\n";
            $content .= "Export Generated At: {$timestamp}\n";
            $content .= "----------------------------------------\n\n";

            foreach ($submission->answers as $userId => $answers) {
                foreach ($answers as $answer) {
                    $label = $answer->label;
                    $val = $answer->value;

                    if (is_array($val) && isset($val['type']) && $val['type'] == 'file') {
                        $content .= "{$label}: [FILE] {$val['file_name']}\n";



                        // An uploaded answer is read only through the startup's own file record
                        // (never from a path built out of the answer text), the exporting user
                        // must be allowed to open it, and every exported file is logged.
                        $files = app(ProtectedFiles::class);
                        $pin = ['company_id' => $submission->company_id];
                        $record = $files->find(ProtectedFileSet::FORM_ANSWER, $val['path'], $pin);
                        $sourcePath = $record && !app(FileAccess::class)->canView(auth()->user(), $record)
                            ? null
                            : $files->localPath(ProtectedFileSet::FORM_ANSWER, $val['path'], $pin);

                        if ($sourcePath) {
                            // keep letters (any script), digits and the extension dot; nothing that could leave the folder
                            $fileName = trim(preg_replace('/[^\p{L}\p{N}._ -]+/u', '_', basename($val['file_name'])), '. ') ?: 'file';
                            $destPath = $startupDir . '/' . $fileName;
                            // Avoid overwrite if multiple files have same name
                            if (File::exists($destPath)) {
                                $destPath = $startupDir . '/' . uniqid() . '_' . $fileName;
                            }
                            File::copy($sourcePath, $destPath);
                            FileAccessLog::create([
                                'user_id' => auth()->id(),
                                'protected_file_id' => optional($record)->id,
                                'action' => 'export',
                                'set' => ProtectedFileSet::FORM_ANSWER,
                                'requested' => $record ? null : $val['path'],
                                'ip' => request()->ip(),
                                'user_agent' => mb_substr((string) request()->userAgent(), 0, 255),
                            ]);
                        } else {
                            $content .= " (File not found during export)\n";
                        }

                    } else {
                        $content .= "{$label}: {$val}\n";
                    }
                }
            }

            File::put($startupDir . "/submission_{$submission->submission_id}.txt", $content);
            $count++;

            // Update Progress
            if ($totalSubmissions > 0) {
                $percentage = round(($count / $totalSubmissions) * 100);
                Cache::put($cacheKey, $percentage, 600);
            }
        }

        // Zip
        $zipFileName = "submissions_export_{$timestamp}.zip";
        $zipPath = $tempDir . '/' . $zipFileName;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $files = File::allFiles($tempDir);
            foreach ($files as $file) {
                $relativePath = $file->getRelativePathname();
                if ($file->getFilename() != $zipFileName) {
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }
            $zip->close();
        }

        // Move Zip to Storage
        $exportPath = $this->getExportPath($organizationId);
        if (!Storage::disk($this->storageDisk)->exists($exportPath)) {
            Storage::disk($this->storageDisk)->makeDirectory($exportPath);
        }

        // Clean old files
        $oldMetaPath = $exportPath . '/metadata.json';
        if (Storage::disk($this->storageDisk)->exists($oldMetaPath)) {
            $oldMeta = json_decode(Storage::disk($this->storageDisk)->get($oldMetaPath), true);
            if (isset($oldMeta['filename'])) {
                Storage::disk($this->storageDisk)->delete($exportPath . '/' . $oldMeta['filename']);
            }
        }

        $finalZipPath = $exportPath . '/' . $zipFileName;
        Storage::disk($this->storageDisk)->put($finalZipPath, File::get($zipPath));

        // Save Metadata
        $metadata = [
            'timestamp' => $timestamp,
            'count' => $count,
            'filename' => $zipFileName
        ];
        Storage::disk($this->storageDisk)->put($exportPath . '/metadata.json', json_encode($metadata));

        // Cleanup Temp
        File::deleteDirectory($tempDir);

        // Clear Progress
        Cache::forget($cacheKey);

        return $metadata;
    }

    public function getProgress($organizationId)
    {
        $cacheKey = 'export_progress_' . ($organizationId ?? 'super_admin');
        return Cache::get($cacheKey, 0);
    }

    public function downloadExport($organizationId)
    {
        $status = $this->getExportStatus($organizationId);
        if ($status['has_export']) {
            $path = $this->getExportPath($organizationId) . '/' . $status['file_name'];
            if (Storage::disk($this->storageDisk)->exists($path)) {
                return response()->download(Storage::disk($this->storageDisk)->path($path));
            }
        }
        return null;
    }

    protected function getExportPath($organizationId)
    {
        return $this->basePath . '/' . ($organizationId ?? 'super_admin');
    }

    protected function getTotalEntries($organizationId)
    {

        $request = new Request();
        return Formdata::formsData($request)->count();
    }

    protected function sanitizeFileName($filename)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    }

    protected function formatSize($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
