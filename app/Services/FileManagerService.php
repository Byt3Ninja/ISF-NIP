<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FileManagerService
{
    /**
     * Default validation rules for files.
     */
    protected array $defaultRules = [
        'file' => 'required|file|max:10240', // 10MB default limit
    ];

    /**
     * Upload a single file.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $disk
     * @return string Relative path of the uploaded file
     */
    public function upload(UploadedFile $file, string $directory, ?string $disk = null): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower($file->getMimeType());

        $blacklistedExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'phps', 
            'asp', 'aspx', 'jsp', 'jspx', 'exe', 'bat', 'cmd', 
            'sh', 'bash', 'pl', 'cgi', 'py', 'html', 'htm', 'svg'
        ];

        $blacklistedMimes = [
            'text/html', 'image/svg+xml', 'application/x-php', 'text/php', 
            'application/x-httpd-php', 'application/x-httpd-php-source', 
            'application/x-msdownload', 'application/x-sh'
        ];

        if (in_array($extension, $blacklistedExtensions) || in_array($mimeType, $blacklistedMimes)) {
            $validator = Validator::make([], []);
            $validator->errors()->add('file', 'File extension or type is not allowed for security reasons.');
            throw new ValidationException($validator);
        }

        $disk = $disk ?? config('filesystems.default');
        
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        return $file->storeAs($directory, $filename, $disk);
    }

    /**
     * Upload multiple files.
     *
     * @param array $files
     * @param string $directory
     * @param string|null $disk
     * @return array Array of relative paths
     */
    public function uploadMultiple(array $files, string $directory, ?string $disk = null): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->upload($file, $directory, $disk);
            }
        }
        return $paths;
    }

    /**
     * Update a single file (delete previous old one).
     *
     * @param UploadedFile $newFile
     * @param string|null $oldPath
     * @param string $directory
     * @param string|null $disk
     * @return string Relative path of the new file
     */
    public function update(UploadedFile $newFile, ?string $oldPath, string $directory, ?string $disk = null): string
    {
        if ($oldPath) {
            $this->delete($oldPath, $disk);
        }
        return $this->upload($newFile, $directory, $disk);
    }

    /**
     * Update multiple files (delete old ones and upload new ones).
     *
     * @param array $newFiles
     * @param array $oldPaths
     * @param string $directory
     * @param string|null $disk
     * @return array Array of new relative paths
     */
    public function updateMultiple(array $newFiles, array $oldPaths = [], string $directory, ?string $disk = null): array
    {
        if (!empty($oldPaths)) {
            $this->delete($oldPaths, $disk);
        }
        return $this->uploadMultiple($newFiles, $directory, $disk);
    }

    /**
     * Delete files from the storage.
     *
     * @param string|array $paths
     * @param string|null $disk
     * @return bool
     */
    public function delete($paths, ?string $disk = null): bool
    {
        if (empty($paths)) {
            return false;
        }

        $disk = $disk ?? config('filesystems.default');
        
        if (is_array($paths)) {
            $paths = array_map(fn($path) => $this->getRelativePath($path, $disk), $paths);
            return Storage::disk($disk)->delete($paths);
        }

        $path = $this->getRelativePath($paths, $disk);
        return Storage::disk($disk)->delete($path);
    }

    /**
     * Validate a file against default or custom rules.
     *
     * @param UploadedFile $file
     * @param array|null $rules
     * @return bool
     * @throws ValidationException
     */
    public function validate(UploadedFile $file, ?array $rules = null): bool
    {
        $rules = $rules ?? $this->defaultRules;
        
        $validator = Validator::make(
            ['file' => $file],
            ['file' => $rules['file'] ?? $rules]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    /**
     * Get the full URL for a relative path.
     *
     * @param string|null $path
     * @param string|null $disk
     * @return string|null
     */
    public function getUrl(?string $path, ?string $disk = null): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        $disk = $disk ?? config('filesystems.default');
        return Storage::disk($disk)->url($path);
    }

    /**
     * Extract the relative path from a full URL if it matches the current storage.
     *
     * @param string $pathOrUrl
     * @param string|null $disk
     * @return string
     */
    public function getRelativePath(string $pathOrUrl, ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.default');
        
        $path = parse_url($pathOrUrl, PHP_URL_PATH) ?? $pathOrUrl;
        
        $diskUrl = Storage::disk($disk)->url('/');
        $diskPathPrefix = parse_url($diskUrl, PHP_URL_PATH) ?? '/';
        $diskPathPrefix = rtrim($diskPathPrefix, '/');
        
        if ($diskPathPrefix && str_starts_with($path, $diskPathPrefix)) {
            $path = substr($path, strlen($diskPathPrefix));
            return ltrim($path, '/');
        }
        
        // Strip leading slash if present for standardizing
        $path = ltrim($path, '/');
        
        // Handle legacy database paths that store prefix like "pitch_decks/12/file.pdf"
        $diskConfig = config("filesystems.disks.{$disk}");
        if ($diskConfig && isset($diskConfig['root'])) {
            $rootFolder = basename($diskConfig['root']);
            if ($rootFolder && str_starts_with($path, $rootFolder . '/')) {
                $path = substr($path, strlen($rootFolder) + 1);
            }
        }
        
        if (str_starts_with($pathOrUrl, '/')) {
            return '/' . ltrim($path, '/');
        }
        
        return ltrim($path, '/');
    }
}
