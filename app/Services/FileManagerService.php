<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class FileManagerService
{
    /** Default validation rules for files. */
    protected array $defaultRules = [
        'file' => 'required|file|max:10240', // 10MB default limit
    ];

    /**
     * Extensions rejected unconditionally, regardless of profile.
     * Cannot be overridden by any allow-list.
     */
    protected array $hardDenyExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar',
        'asp', 'aspx', 'jsp', 'jspx', 'jspa',
        'exe', 'dll', 'bat', 'cmd', 'com', 'msi',
        'sh', 'bash', 'zsh', 'ksh', 'pl', 'cgi', 'py', 'rb', 'ps1',
        'htaccess', 'htpasswd', 'shtml', 'xhtml', 'mhtml', 'html', 'htm',
        'svg', 'swf', 'jar', 'war',
    ];

    /** MIME types rejected unconditionally, regardless of profile. */
    protected array $hardDenyMimes = [
        'text/html', 'application/xhtml+xml', 'image/svg+xml',
        'application/x-php', 'text/php', 'application/x-httpd-php',
        'application/x-httpd-php-source', 'application/x-msdownload',
        'application/x-sh', 'application/x-shellscript',
        'application/x-perl', 'application/x-python',
        'application/java-archive', 'application/x-msdos-program',
    ];

    /** Per-context allow-lists: extensions + acceptable real-content MIME types. */
    protected array $profiles = [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt'],
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain', 'text/csv',
                // office (zip-container) files are sometimes sniffed generically;
                // safe only because the extension gate already restricts to office types.
                'application/zip', 'application/octet-stream',
            ],
        ],
        'media' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov', 'ogg'],
            'mimes' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'video/mp4', 'video/webm', 'video/quicktime', 'video/ogg', 'application/ogg',
            ],
        ],
        'csv' => [
            'extensions' => ['csv', 'txt'],
            'mimes' => ['text/plain', 'text/csv', 'application/csv', 'application/octet-stream'],
        ],
        'common' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt'],
            'mimes' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain', 'text/csv', 'application/zip', 'application/octet-stream',
            ],
        ],
        // `common` plus zip/rar archives, for task attachments and shared team files.
        // Not part of the cert baseline: it exists so those existing features keep working.
        'attachment' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'zip', 'rar'],
            'mimes' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain', 'text/csv', 'application/csv', 'application/zip', 'application/octet-stream',
                'application/x-zip-compressed', 'application/x-rar', 'application/x-rar-compressed', 'application/vnd.rar',
            ],
        ],
    ];

    /** True if extension or real MIME is on the unconditional deny-list. */
    public function isHardDenied(string $ext, string $mime): bool
    {
        return in_array(strtolower($ext), $this->hardDenyExtensions, true)
            || in_array(strtolower($mime), $this->hardDenyMimes, true);
    }

    /** Allow-lists for a named profile. Throws on unknown profile (fail closed). */
    public function allowedFor(string $profile): array
    {
        if (!isset($this->profiles[$profile])) {
            throw new InvalidArgumentException("Unknown upload profile: {$profile}");
        }
        return $this->profiles[$profile];
    }

    /**
     * Run deny-list backstop, then profile extension + real-content-MIME gates.
     * Returns the validated lowercase extension. Throws ValidationException on rejection.
     */
    public function assertAllowed(UploadedFile $file, string $profile = 'common'): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        $realMime = '';
        $path = $file->getRealPath();
        if ($path && is_readable($path)) {
            $info = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = strtolower((string) $info->file($path));

            // finfo may return application/octet-stream for valid image files that
            // have a correct magic signature but insufficient content for deep sniffing.
            // getimagesize() uses magic-byte image detection and is a reliable fallback
            // for the image profile. PHP/script content returns false from getimagesize().
            if ($realMime === 'application/octet-stream') {
                $imgSize = @getimagesize($path);
                if ($imgSize && !empty($imgSize['mime'])) {
                    $realMime = strtolower($imgSize['mime']);
                }
            }
        }
        if ($realMime === '' || $realMime === 'application/x-empty' || $realMime === 'inode/x-empty') {
            $realMime = strtolower((string) $file->getMimeType());
        }

        // 1. Hard deny-list — unconditional.
        // 2. Profile extension allow-list.
        // 3. Profile real-content-MIME allow-list.
        $allowed = $this->allowedFor($profile);
        $ok = !$this->isHardDenied($ext, $realMime)
            && $realMime !== ''
            && in_array($ext, $allowed['extensions'], true)
            && in_array($realMime, $allowed['mimes'], true);

        if (!$ok) {
            $validator = Validator::make([], []);
            $validator->errors()->add('file', 'File extension or type is not allowed for security reasons.');
            throw new ValidationException($validator);
        }

        return $ext;
    }

    /**
     * Validate several uploads of one request up front. Forms that replace more than one
     * file call this before touching anything, so a single rejected file refuses the whole
     * request instead of leaving earlier files already replaced (and their old copies gone).
     *
     * @param array<int, array{0: mixed, 1: string}> $uploads pairs of [file|null, profile]
     */
    public function assertAllAllowed(array $uploads): void
    {
        foreach ($uploads as [$file, $profile]) {
            if ($file instanceof UploadedFile) {
                $this->assertAllowed($file, $profile);
            }
        }
    }

    /**
     * Build a server-controlled, collision-resistant filename for a validated extension.
     * Single source of truth for the stored-filename format.
     */
    public function safeName(string $ext): string
    {
        return time() . '_' . uniqid() . '.' . $ext;
    }

    /**
     * Validate a file against a profile and return a safe server-generated filename,
     * WITHOUT storing it. Use for legacy paths that must keep moving the file into
     * public_path() (or another non-disk destination) but still need the whitelist,
     * content-MIME verification, and a non-client filename.
     *
     * @return string Safe filename (e.g. "1718000000_64f3a1b2c3.png"); throws on rejection.
     */
    public function validatedSafeName(UploadedFile $file, string $profile = 'common'): string
    {
        $ext = $this->assertAllowed($file, $profile);
        return $this->safeName($ext);
    }

    public function upload(UploadedFile $file, string $directory, ?string $disk = null, string $profile = 'common'): string
    {
        $ext = $this->assertAllowed($file, $profile);

        $disk = $disk ?? config('filesystems.default');
        $filename = $this->safeName($ext);

        return $file->storeAs($directory, $filename, $disk);
    }

    public function uploadMultiple(array $files, string $directory, ?string $disk = null, string $profile = 'common'): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->upload($file, $directory, $disk, $profile);
            }
        }
        return $paths;
    }

    public function update(UploadedFile $newFile, ?string $oldPath, string $directory, ?string $disk = null, string $profile = 'common'): string
    {
        // Validate first: a rejected replacement must never cost the user their existing file.
        $this->assertAllowed($newFile, $profile);

        if ($oldPath) {
            $this->delete($oldPath, $disk);
        }
        return $this->upload($newFile, $directory, $disk, $profile);
    }

    public function updateMultiple(array $newFiles, array $oldPaths, string $directory, ?string $disk = null, string $profile = 'common'): array
    {
        // Validate every replacement first: a rejected batch must never cost the existing files.
        foreach ($newFiles as $file) {
            if ($file instanceof UploadedFile) {
                $this->assertAllowed($file, $profile);
            }
        }

        if (!empty($oldPaths)) {
            $this->delete($oldPaths, $disk);
        }
        return $this->uploadMultiple($newFiles, $directory, $disk, $profile);
    }

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

        $path = ltrim($path, '/');

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
