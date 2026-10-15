<?php

namespace App\Services\ProtectedFiles;

use App\ProtectedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Moves existing confidential files from their old web-served location to the
 * private disks, in separate, repeatable steps that never delete anything:
 *
 *   plan()   classify every database reference (shared by all steps)
 *   copy     copy + checksum + record          (old file stays where it is)
 *   verify   every reference resolves privately, bytes identical
 *   retire   move the old public copy into a quarantine folder
 *   restore  put quarantined files back        (rollback)
 */
class LegacyMigration
{
    public const COPY = 'copy';          // file is on the old location, not yet private
    public const DONE = 'done';          // recorded and present on the private disk
    public const MISSING = 'missing';    // referenced, but there is no file anywhere (already broken)
    public const NOT_A_FILE = 'not_a_file';
    public const SHARED = 'shared';      // one file, several owners, and no way to tell whose it is
    public const OVERWRITTEN = 'overwritten'; // an older upload replaced on disk by a later same-named one
    public const FOREIGN = 'foreign';    // the path is recorded for a different owner

    public function __construct(protected ProtectedFiles $files, protected LegacyReferences $references)
    {
    }

    /**
     * Every reference of a set with what has to happen to it.
     *
     * @return array<int, array> reference + ['path' => normalized|null, 'state' => one of the constants, 'source' => abs path]
     */
    public function plan(string $set): array
    {
        $items = [];
        $owners = [];
        foreach ($this->references->each($set) as $reference) {
            $path = $this->files->normalize($set, $reference['value']);
            $reference['path'] = $path;
            $reference['source'] = $path === null ? null : $this->files->legacyPath($set, $path);
            if ($path !== null) {
                $owners[$path][$reference['owner_type'] . ':' . $reference['owner_id']] = $reference['stamp'] ?? null;
            }
            $items[] = $reference;
        }

        // Old application uploads kept the applicant's own file name in one shared folder, so a
        // later upload with the same name replaced the earlier file. The bytes on disk therefore
        // belong to whoever wrote last: that owner keeps the file, the earlier rows lost theirs
        // long before this migration. Without timestamps the case is left for a person.
        $keeper = [];
        foreach ($owners as $path => $who) {
            if (count($who) > 1 && !in_array(null, $who, true) && count(array_unique($who)) === count($who)) {
                arsort($who);
                $keeper[$path] = array_key_first($who);
            }
        }

        $disk = $this->files->disk($set);
        foreach ($items as &$item) {
            if ($item['path'] === null) {
                $item['state'] = self::NOT_A_FILE;
                continue;
            }
            $me = $item['owner_type'] . ':' . $item['owner_id'];
            if (isset($keeper[$item['path']]) && $keeper[$item['path']] !== $me) {
                // checked first: once the keeper's file is recorded, this row must not read as a conflict
                $item['state'] = self::OVERWRITTEN;
                continue;
            }

            $record = ProtectedFile::withTrashed()->where('disk', $disk)->where('path', $item['path'])->first();
            if ($record) {
                $mine = $record->owner_type === $item['owner_type'] && (int) $record->owner_id === (int) $item['owner_id'];
                $item['state'] = $mine ? self::DONE : self::FOREIGN;
            } elseif (count($owners[$item['path']]) > 1 && !isset($keeper[$item['path']])) {
                $item['state'] = self::SHARED;
            } elseif (is_file($item['source'])) {
                $item['state'] = self::COPY;
            } else {
                $item['state'] = self::MISSING;
            }
        }
        unset($item);

        return $items;
    }

    /**
     * Copy one referenced file to the private disk, confirm the copy is
     * byte-identical, and record it. The source is left untouched.
     */
    public function copy(string $set, array $item): ProtectedFile
    {
        $disk = Storage::disk($this->files->disk($set));
        $target = $disk->path($item['path']);

        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true) && !is_dir(dirname($target))) {
            throw new \RuntimeException('Cannot create ' . dirname($target));
        }
        if (!is_file($target) && !copy($item['source'], $target)) {
            throw new \RuntimeException('Copy failed: ' . $item['source']);
        }
        if (hash_file('sha256', $target) !== hash_file('sha256', $item['source'])) {
            throw new \RuntimeException('Checksum mismatch after copy: ' . $item['source']);
        }

        return $this->files->register($set, $item['path'], [
            'owner_type' => $item['owner_type'],
            'owner_id' => $item['owner_id'],
            'company_id' => $item['company_id'],
            'cycle_id' => $item['cycle_id'],
            'field' => $item['field'],
            'original_name' => $item['name'],
            'legacy_path' => $this->legacyUrlPath($set, $item['path']),
        ]);
    }

    /** The URL path the file used to be served at, without a leading slash. */
    public function legacyUrlPath(string $set, string $path): string
    {
        return ltrim((string) parse_url($this->files->legacyUrl($set, $path) ?? '', PHP_URL_PATH), '/');
    }

    /** Where a retired public file is parked: {private root}/_retired/{its old URL path}. */
    public function retiredPath(string $legacyUrlPath): string
    {
        return rtrim(config('protected_files.root'), '/') . '/_retired/' . $legacyUrlPath;
    }

    /** Where an unreferenced public file is parked. */
    public function orphanPath(string $set, string $relative): string
    {
        return rtrim(config('protected_files.root'), '/') . '/_orphans/' . $set . '/' . $relative;
    }

    /** Move a file, creating the destination folder; never overwrites. */
    public function move(string $from, string $to): bool
    {
        if (!is_file($from) || file_exists($to)) {
            return false;
        }
        if (!is_dir(dirname($to)) && !mkdir(dirname($to), 0775, true) && !is_dir(dirname($to))) {
            return false;
        }

        return rename($from, $to);
    }
}
