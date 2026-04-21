<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\ImporterFieldSchema;
use PHPUnit\Framework\TestCase;

class ImporterFieldSchemaTest extends TestCase
{
    public function test_public_api_methods_exist(): void
    {
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'all'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'required'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'bySection'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'find'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'allAliases'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'crossFieldRules'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'templateHeaders'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'templateSampleRows'));
    }

    public function test_section_field_counts_match_spec(): void
    {
        $grouped = ImporterFieldSchema::bySection();

        $this->assertCount(4, $grouped[ImporterFieldSchema::SECTION_PROGRAM],     'program section');
        $this->assertCount(11, $grouped[ImporterFieldSchema::SECTION_FOUNDERS],   'founders section');
        $this->assertCount(5, $grouped[ImporterFieldSchema::SECTION_CO_FOUNDERS],'co_founders section');
        $this->assertCount(24, $grouped[ImporterFieldSchema::SECTION_COMPANY],   'company section');
        $this->assertCount(3, $grouped[ImporterFieldSchema::SECTION_EMPLOYEES],  'employees section');
        $this->assertCount(11, $grouped[ImporterFieldSchema::SECTION_FINANCIALS],'financials section');

        $this->assertCount(58, ImporterFieldSchema::all(), 'total field count');
    }

    public function test_every_field_has_required_keys(): void
    {
        $requiredKeys = ['key', 'label_en', 'label_ar', 'section', 'required',
                         'auto_map_aliases', 'validator', 'target', 'template_sample'];

        foreach (ImporterFieldSchema::all() as $key => $field) {
            foreach ($requiredKeys as $rk) {
                $this->assertArrayHasKey($rk, $field, "Field '{$key}' missing '{$rk}'");
            }
            $this->assertSame($key, $field['key'], "Field key mismatch for '{$key}'");
            $this->assertContains($field['section'], ImporterFieldSchema::SECTION_ORDER);
            $this->assertIsBool($field['required']);
            $this->assertIsArray($field['auto_map_aliases']);
            $this->assertIsArray($field['target']);
            $this->assertArrayHasKey('table', $field['target']);
            $this->assertArrayHasKey('column', $field['target']);
        }
    }

    public function test_alias_map_has_no_unexpected_collisions(): void
    {
        // Same alias pointing to two fields = collision. First-wins is expected behavior,
        // but we assert the count so adding a field that collides triggers a deliberate review.
        $aliases = ImporterFieldSchema::allAliases();
        $fieldKeys = array_unique(array_values($aliases));
        $this->assertGreaterThan(100, count($aliases), 'alias map should cover many terms');
        $this->assertSame(58, count($fieldKeys), 'every field contributes at least one unique alias');
    }

    public function test_required_fields_match_spec(): void
    {
        $required = array_keys(ImporterFieldSchema::required());
        sort($required);

        $expected = [
            'country', 'founder_country_code', 'founder_email', 'founder_first_name',
            'founder_gender', 'founder_last_name', 'founder_phone', 'program_end_date',
            'program_name', 'program_start_date', 'program_type', 'startup_name',
        ];
        sort($expected);

        $this->assertSame($expected, $required);
    }

    public function test_template_sample_rows_match_header_width(): void
    {
        $headers = ImporterFieldSchema::templateHeaders();
        $rows    = ImporterFieldSchema::templateSampleRows();

        $this->assertCount(2, $rows, 'template should ship 2 sample rows (general + hackathon)');
        foreach ($rows as $i => $row) {
            $this->assertCount(count($headers), $row, "sample row {$i} width mismatch");
        }
    }

    public function test_cross_field_rules_have_expected_keys(): void
    {
        $rules = ImporterFieldSchema::crossFieldRules();
        $expected = [
            'program_dates_order',
            'cofounder_requires_name',
            'cofounder_requires_email',
            'cofounder_email_distinct',
            'state_belongs_to_country',
            'prev_incubator_requires_flag',
            'male_employees_le_total',
            'female_employees_le_total',
            'male_plus_female_le_total',
            'financials_require_currency',
            'founder_dob_not_future',
        ];
        foreach ($expected as $ruleKey) {
            $this->assertArrayHasKey($ruleKey, $rules, "missing rule '{$ruleKey}'");
        }
    }
}
