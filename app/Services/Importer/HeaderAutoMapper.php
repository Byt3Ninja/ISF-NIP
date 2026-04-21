<?php

namespace App\Services\Importer;

/**
 * Maps CSV headers to ImporterFieldSchema field_keys using the schema's alias table.
 *
 * Returns:
 *   [
 *     'mappings'           => ['field_key' => int|null, ... every field_key present],
 *     'matched_count'      => int,
 *     'unmatched_required' => string[],
 *     'unmatched_optional' => string[],
 *   ]
 */
class HeaderAutoMapper
{
    public function match(array $headers): array
    {
        $aliases = ImporterFieldSchema::allAliases();

        $mappings = array_fill_keys(array_keys(ImporterFieldSchema::all()), null);

        foreach ($headers as $i => $header) {
            $normalised = ImporterFieldSchema::normaliseAlias((string) $header);
            if ($normalised === '') {
                continue;
            }

            $fieldKey = $aliases[$normalised] ?? null;
            if ($fieldKey === null) {
                continue;
            }

            // First-wins: skip if already mapped
            if ($mappings[$fieldKey] !== null) {
                continue;
            }

            $mappings[$fieldKey] = $i;
        }

        $matchedCount = count(array_filter($mappings, fn ($v) => $v !== null));

        $unmatchedRequired = [];
        $unmatchedOptional = [];
        foreach (ImporterFieldSchema::all() as $key => $field) {
            if ($mappings[$key] !== null) {
                continue;
            }
            if (!empty($field['required'])) {
                $unmatchedRequired[] = $key;
            } else {
                $unmatchedOptional[] = $key;
            }
        }

        return [
            'mappings'           => $mappings,
            'matched_count'      => $matchedCount,
            'unmatched_required' => $unmatchedRequired,
            'unmatched_optional' => $unmatchedOptional,
        ];
    }
}
