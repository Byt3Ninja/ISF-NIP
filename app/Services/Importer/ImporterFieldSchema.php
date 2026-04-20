<?php

namespace App\Services\Importer;

/**
 * Single source of truth for all importable fields.
 *
 * Every consumer (template generator, mapping UI, auto-mapper, row validator,
 * row importer, generated docs) reads from this class. Adding a field is a
 * one-file edit.
 *
 * Field entry shape:
 *   [
 *     'key'              => 'founder_email',
 *     'label_en'         => 'Founder Email',
 *     'label_ar'         => 'البريد الإلكتروني للمؤسس',
 *     'section'          => 'founders',
 *     'required'         => true,
 *     'auto_map_aliases' => ['E-Mail', 'Founder Email', 'Email'],
 *     'validator'        => 'email',
 *     'allowed_values'   => null,  // for enum validators only
 *     'target'           => ['table' => 'users', 'column' => 'email'],
 *     'template_sample'  => 'founder1@example.com',
 *     'notes'            => null,
 *   ]
 */
class ImporterFieldSchema
{
    public const SECTION_PROGRAM     = 'program';
    public const SECTION_FOUNDERS    = 'founders';
    public const SECTION_CO_FOUNDERS = 'co_founders';
    public const SECTION_COMPANY     = 'company';
    public const SECTION_EMPLOYEES   = 'employees';
    public const SECTION_FINANCIALS  = 'financials';

    public const SECTION_ORDER = [
        self::SECTION_PROGRAM,
        self::SECTION_FOUNDERS,
        self::SECTION_CO_FOUNDERS,
        self::SECTION_COMPANY,
        self::SECTION_EMPLOYEES,
        self::SECTION_FINANCIALS,
    ];

    /** @return array<string, array> */
    public static function all(): array
    {
        return []; // populated in Task 6
    }

    /** @return array<string, array> */
    public static function required(): array
    {
        return array_filter(self::all(), fn($f) => $f['required'] === true);
    }

    /** @return array<string, array<string, array>> */
    public static function bySection(): array
    {
        $grouped = array_fill_keys(self::SECTION_ORDER, []);
        foreach (self::all() as $key => $field) {
            $grouped[$field['section']][$key] = $field;
        }
        return $grouped;
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** @return array<string, string> lowercased_alias => field_key */
    public static function allAliases(): array
    {
        $aliases = [];
        foreach (self::all() as $key => $field) {
            $candidates = array_merge(
                [$field['label_en'], $field['label_ar']],
                $field['auto_map_aliases'] ?? []
            );
            foreach ($candidates as $alias) {
                $normalised = self::normaliseAlias($alias);
                if ($normalised === '') {
                    continue;
                }
                // First-wins on collision (order of fields in all() determines precedence)
                if (!isset($aliases[$normalised])) {
                    $aliases[$normalised] = $key;
                }
            }
        }
        return $aliases;
    }

    public static function crossFieldRules(): array
    {
        return []; // populated in Task 6
    }

    /** @return string[] ordered CSV header row for the downloadable template */
    public static function templateHeaders(): array
    {
        return array_map(fn($f) => $f['label_en'], self::all());
    }

    /** @return array<int, array<int, string>> two example data rows */
    public static function templateSampleRows(): array
    {
        return []; // populated in Task 6
    }

    public static function normaliseAlias(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', trim($s));
        $s = mb_strtolower($s, 'UTF-8');
        return preg_replace('/[^a-z0-9\p{Arabic}\s]/u', '', $s);
    }
}
