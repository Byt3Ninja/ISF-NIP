<?php

namespace App\Traits;

use App\Services\ProtectedFiles\FileAccess;
use App\Services\ProtectedFiles\LegacyReferences;
use App\Services\ProtectedFiles\ProtectedFiles;

/**
 * For models that keep a JSON list of confidential documents
 * ([{name, path, url}, …]) in a column.
 *
 * The `url` saved in the row is ignored: it is recomputed on every read from the
 * entry's file record, so it always points at the authorized file route, and it
 * is null for a viewer the route would refuse. Entries are never dropped here
 * (write paths read this list back to rebuild it).
 */
trait HasProtectedDocuments
{
    protected function protectedDocumentList($raw, string $set, string $ownerType): array
    {
        $files = app(ProtectedFiles::class);
        $access = app(FileAccess::class);
        $viewer = auth()->user();
        $pin = ['owner_type' => $ownerType, 'owner_id' => $this->getKey()];

        $list = [];
        foreach (app(LegacyReferences::class)->entries($raw) as $entry) {
            if (is_string($entry)) {
                $entry = ['name' => basename($entry), 'path' => $entry];
            }
            if (!is_array($entry)) {
                continue;
            }
            $value = $entry['path'] ?? $entry['url'] ?? null;
            $record = $files->find($set, $value, $pin);
            $entry['url'] = $record && !$access->canView($viewer, $record)
                ? null
                : $files->urlFor($set, $value, $pin);
            $list[] = $entry;
        }

        return $list;
    }
}
