<?php

namespace App\Services\ProtectedFiles;

use App\Enums\ProtectedFileSet;
use App\ProtectedFile;
use App\Services\FileManagerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Stores, finds, links and retires confidential uploads.
 *
 * The owning tables (companies, form_data, milestones, …) keep the path strings
 * they always stored. Those strings are user-influenced, so they are never used
 * to locate or authorize a file directly: a value is first resolved to its
 * protected_files record, and everything else works from that record.
 */
class ProtectedFiles
{
    /** Relative-path shape each set must have on its disk; anything else is not a file of that set. */
    protected const SHAPES = [
        ProtectedFileSet::COMPANY_LEGAL      => '#^\d+/.+$#',
        ProtectedFileSet::COMPANY_PITCH      => '#^\d+/.+$#',
        ProtectedFileSet::FORM_ANSWER        => '#^[^/]+$#',
        ProtectedFileSet::MILESTONE_DOCUMENT => '#^milestones/\d+/[^/]+$#',
        ProtectedFileSet::EMPLOYEE_DOCUMENT  => '#^(employees/\d+/documents|employee_documents)/[^/]+$#',
        ProtectedFileSet::TASK_ATTACHMENT    => '#^tasks/attachments/[^/]+$#',
        ProtectedFileSet::PROGRAM_GUIDELINE  => '#^programs/[^/]+$#',
    ];

    public function __construct(protected FileManagerService $files)
    {
    }

    public function config(string $set): array
    {
        $config = config("protected_files.sets.$set");
        if (!$config) {
            throw new InvalidArgumentException("Unknown protected file set: {$set}");
        }

        return $config;
    }

    public function disk(string $set): string
    {
        return $this->config($set)['disk'];
    }

    // ------------------------------------------------------------------ writing

    /**
     * Validate an upload against a whitelist profile, store it on the set's
     * private disk under a server-generated name, and record it.
     *
     * @param array $owner owner_type, owner_id and, where they apply, company_id, cycle_id, field
     */
    public function store(UploadedFile $file, string $set, string $directory, array $owner, string $profile = 'common'): ProtectedFile
    {
        $disk = $this->disk($set);
        $path = $this->files->upload($file, $directory, $disk, $profile);

        return $this->register($set, $path, $owner + [
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Store a replacement and return its stored path. The new file is validated
     * and written first; only then is the file the column pointed at retired.
     * `$owner` must carry what pins the old value to this record (company_id or
     * owner_type + owner_id), so a forged column value can never retire someone
     * else's file.
     */
    public function replace(UploadedFile $file, $currentValue, string $set, string $directory, array $owner, string $profile = 'common'): string
    {
        $new = $this->store($file, $set, $directory, $owner, $profile);
        $this->retireValue($set, $currentValue, $this->pin($owner));

        return $new->path;
    }

    /**
     * Retire the file a stored value refers to, if it has a record that belongs
     * to the expected owner. A value without a record (an old public file not
     * yet migrated) is left where it is; the migration quarantines it later.
     */
    public function retireValue(string $set, $value, array $expect): void
    {
        $this->trash($this->find($set, $value, $expect));
    }

    /** Retire every file matching the given record attributes (e.g. all files of a deleted company). */
    public function retireWhere(array $attributes): void
    {
        if (!$attributes) {
            return;
        }
        ProtectedFile::where($attributes)->get()->each(fn (ProtectedFile $file) => $this->trash($file));
    }

    /** The attributes that tie a file record to the row referencing it. */
    public function pin(array $owner): array
    {
        return isset($owner['company_id'])
            ? ['company_id' => $owner['company_id']]
            : ['owner_type' => $owner['owner_type'], 'owner_id' => $owner['owner_id']];
    }

    /**
     * Record a file that is already on the set's private disk (used by store()
     * and by the migration of existing files).
     */
    public function register(string $set, string $path, array $attributes): ProtectedFile
    {
        $disk = $this->disk($set);

        if (!preg_match(self::SHAPES[$set], $path)) {
            throw new InvalidArgumentException("Path does not belong to set {$set}: {$path}");
        }
        foreach (['owner_type', 'owner_id'] as $required) {
            if (empty($attributes[$required])) {
                throw new InvalidArgumentException("A protected file needs {$required}");
            }
        }

        $absolute = Storage::disk($disk)->path($path);
        $exists = is_file($absolute);

        return ProtectedFile::create(array_merge($attributes, [
            'set' => $set,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $this->cleanName($attributes['original_name'] ?? null) ?: basename($path),
            'mime' => $exists ? (new \finfo(FILEINFO_MIME_TYPE))->file($absolute) ?: null : null,
            'size' => $exists ? filesize($absolute) : null,
            'sha256' => $exists ? hash_file('sha256', $absolute) : null,
        ]));
    }

    /**
     * Make sure the file a column refers to has a record. For uploads made before
     * their owner existed (a new program is saved after its files are uploaded):
     * the bytes are already on the set's private disk, written there by server
     * code. Never adopts a path that is already recorded for somebody else.
     */
    public function ensureRecorded(string $set, $value, array $owner): ?ProtectedFile
    {
        $path = $this->normalize($set, $value);
        if ($path === null) {
            return null;
        }
        $disk = $this->disk($set);

        if (ProtectedFile::withTrashed()->where('disk', $disk)->where('path', $path)->exists()) {
            return $this->find($set, $value, $this->pin($owner));
        }
        if (!Storage::disk($disk)->exists($path)) {
            return null; // still an old public file: the migration records those
        }

        return $this->register($set, $path, $owner + ['uploaded_by' => auth()->id()]);
    }

    /**
     * Take a file out of use without destroying it: the bytes move to the trash
     * disk and the record is soft-deleted. Nothing is ever unlinked here.
     */
    public function trash(?ProtectedFile $file): void
    {
        if (!$file || $file->trashed()) {
            return;
        }

        $source = Storage::disk($file->disk);
        if ($source->exists($file->path)) {
            $target = $file->uuid . '/' . basename($file->path);
            $trash = Storage::disk('protected_trash');
            $trash->makeDirectory($file->uuid);
            rename($source->path($file->path), $trash->path($target));

            $file->disk = 'protected_trash';
            $file->path = $target;
            $file->save();
        }

        $file->delete();
    }

    // ------------------------------------------------------------------ resolving

    /**
     * Turn a stored value into the relative path it denotes on the set's disk,
     * or null when it is not a file path of that set (empty, the literal "null",
     * free text, a traversal attempt, …). Tolerates the legacy shapes: leading
     * slash, absolute URL, a leading disk-folder name, a doubled first segment.
     */
    public function normalize(string $set, $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || strtolower($value) === 'null') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }
        $value = ltrim(rawurldecode($value), '/');

        if ($value === '' || str_contains($value, '\\') || str_contains($value, "\0") || preg_match('#(^|/)\.\.?(/|$)#', $value)) {
            return null;
        }

        foreach ($this->config($set)['legacy_prefixes'] ?? [] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = substr($value, strlen($prefix));
                break;
            }
        }
        // "financial_plan/7/financial_plan/x.pdf" and the like: an old writer repeated a folder name in front.
        if (preg_match('#^([A-Za-z_]+)/(\d+/\1/.+)$#', $value, $m)) {
            $value = $m[2];
        }

        return preg_match(self::SHAPES[$set], $value) ? $value : null;
    }

    /**
     * The record behind a stored value, or null. `$expect` pins the record to the
     * row that references it (e.g. ['company_id' => 7]); a value that resolves to
     * somebody else's file is treated as no file at all.
     */
    public function find(string $set, $value, array $expect = []): ?ProtectedFile
    {
        $path = $this->normalize($set, $value);
        if ($path === null) {
            return null;
        }

        $file = ProtectedFile::where('set', $set)->where('disk', $this->disk($set))->where('path', $path)->first();
        if (!$file) {
            return null;
        }
        foreach ($expect as $attribute => $expected) {
            if ((string) $file->{$attribute} !== (string) $expected) {
                return null;
            }
        }

        return $file;
    }

    // ------------------------------------------------------------------ links

    /** The one URL a confidential file is reachable at (web session). */
    public function url(?ProtectedFile $file, bool $download = false): ?string
    {
        if (!$file) {
            return null;
        }

        return route('protected_files.show', array_filter(['uuid' => $file->uuid, 'download' => $download ? 1 : null]));
    }

    /** Same, for API clients that authenticate with a bearer token. */
    public function apiUrl(?ProtectedFile $file): ?string
    {
        return $file ? route('api.protected_files.show', ['uuid' => $file->uuid]) : null;
    }

    /**
     * Link for a stored value. While existing files are still being moved
     * (protected_files.legacy_fallback), a value without a record keeps its old
     * public link; afterwards it gets none.
     */
    public function urlFor(string $set, $value, array $expect = [], bool $api = false): ?string
    {
        if ($file = $this->find($set, $value, $expect)) {
            return $api ? $this->apiUrl($file) : $this->url($file);
        }

        return config('protected_files.legacy_fallback') ? $this->legacyUrl($set, $value) : null;
    }

    /** Where the file used to be served from before it was made private. */
    public function legacyUrl(string $set, $value): ?string
    {
        $path = $this->normalize($set, $value);
        if ($path === null) {
            return null;
        }
        $config = $this->config($set);

        return isset($config['legacy_public_dir'])
            ? asset($config['legacy_public_dir'] . '/' . $path)
            : Storage::disk($config['legacy_disk'])->url($path);
    }

    /**
     * Absolute path of the bytes behind a stored value, for server-side readers
     * (exports). Only a recorded file of the expected owner is returned; during
     * the transition an unrecorded value may still resolve to its old public copy.
     */
    public function localPath(string $set, $value, array $expect = []): ?string
    {
        if ($file = $this->find($set, $value, $expect)) {
            $path = Storage::disk($file->disk)->path($file->path);

            return is_file($path) ? $path : null;
        }
        if (!config('protected_files.legacy_fallback')) {
            return null;
        }
        $path = $this->normalize($set, $value);
        $legacy = $path === null ? null : $this->legacyPath($set, $path);

        return $legacy !== null && is_file($legacy) ? $legacy : null;
    }

    /** Absolute location of the old public copy (may not exist). */
    public function legacyPath(string $set, string $path): string
    {
        $config = $this->config($set);

        return isset($config['legacy_public_dir'])
            ? rtrim(config('protected_files.legacy_public_root'), '/') . '/' . $config['legacy_public_dir'] . '/' . $path
            : Storage::disk($config['legacy_disk'])->path($path);
    }

    /** A display name safe to keep and to offer as a download name. */
    protected function cleanName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }
        $name = trim(preg_replace('#[\x00-\x1F\x7F/\\\\%]+#u', ' ', basename(str_replace('\\', '/', $name))));

        return $name === '' ? null : Str::limit($name, 200, '');
    }
}
