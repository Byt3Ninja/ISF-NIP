<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\HeaderAutoMapper;
use App\Services\Importer\ImporterFieldSchema;
use PHPUnit\Framework\TestCase;

class HeaderAutoMapperTest extends TestCase
{
    private HeaderAutoMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new HeaderAutoMapper();
    }

    public function test_returns_result_with_all_schema_keys(): void
    {
        $result = $this->mapper->match(['Program Name']);
        foreach (array_keys(ImporterFieldSchema::all()) as $k) {
            $this->assertArrayHasKey($k, $result['mappings'], "missing mapping slot for {$k}");
        }
    }

    public function test_matches_exact_label_headers(): void
    {
        $headers = ['Program Name', 'Program Type', 'Founder Email', 'Startup Name', 'Country'];
        $result = $this->mapper->match($headers);
        $this->assertSame(0, $result['mappings']['program_name']);
        $this->assertSame(1, $result['mappings']['program_type']);
        $this->assertSame(2, $result['mappings']['founder_email']);
        $this->assertSame(3, $result['mappings']['startup_name']);
        $this->assertSame(4, $result['mappings']['country']);
        $this->assertSame(5, $result['matched_count']);
    }

    public function test_matches_legacy_aliases(): void
    {
        $headers = ['type', 'E-Mail', 'Phone', 'Gender', 'country code', 'Governorate', 'industry', 'Establishment date', 'Product or Service', 'Target Markets', 'Founder Name', 'Founder Last Name'];
        $result = $this->mapper->match($headers);
        $this->assertSame(0, $result['mappings']['program_type'], 'type => program_type');
        $this->assertSame(1, $result['mappings']['founder_email']);
        $this->assertSame(2, $result['mappings']['founder_phone']);
        $this->assertSame(3, $result['mappings']['founder_gender']);
        $this->assertSame(4, $result['mappings']['founder_country_code']);
        $this->assertSame(5, $result['mappings']['governorate']);
        $this->assertSame(6, $result['mappings']['industry']);
        $this->assertSame(7, $result['mappings']['establishment_date']);
        $this->assertSame(8, $result['mappings']['product_or_service']);
        $this->assertSame(9, $result['mappings']['target_markets']);
        $this->assertSame(10, $result['mappings']['founder_first_name'], 'Founder Name => founder_first_name');
        $this->assertSame(11, $result['mappings']['founder_last_name']);
    }

    public function test_is_case_insensitive_and_whitespace_tolerant(): void
    {
        $result = $this->mapper->match(['  program   NAME  ', 'FOUNDER email']);
        $this->assertSame(0, $result['mappings']['program_name']);
        $this->assertSame(1, $result['mappings']['founder_email']);
    }

    public function test_unmatched_headers_leave_null_mappings(): void
    {
        $result = $this->mapper->match(['Widget', 'Gadget']);
        $this->assertNull($result['mappings']['program_name']);
        $this->assertNull($result['mappings']['founder_email']);
        $this->assertSame(0, $result['matched_count']);
    }

    public function test_reports_unmatched_required_and_optional_split(): void
    {
        $result = $this->mapper->match(['Program Name']);
        $this->assertContains('program_type', $result['unmatched_required']);
        $this->assertContains('startup_name', $result['unmatched_required']);
        $this->assertContains('founder_national_id', $result['unmatched_optional']);
        $this->assertNotContains('program_name', $result['unmatched_required']);
        $this->assertNotContains('program_name', $result['unmatched_optional']);
    }

    public function test_first_header_wins_on_alias_conflict(): void
    {
        $result = $this->mapper->match(['Founder Email', 'E-Mail']);
        $this->assertSame(0, $result['mappings']['founder_email'], 'first winning header index is 0');
    }

    public function test_matches_arabic_label_as_alias(): void
    {
        $result = $this->mapper->match(['اسم البرنامج']);
        $this->assertSame(0, $result['mappings']['program_name'], 'Arabic label should match');
    }

    public function test_matches_all_financial_headers(): void
    {
        $headers = [
            'Currency', 'Does Business Generate Revenue', 'Average Annual Revenue',
            'Current Operating Income (EBIT)', 'Forecast Operating Income Year 1 (EBIT)',
            'Forecast Operating Income Year 2 (EBIT)', 'Forecast Operating Income Year 3 (EBIT)',
            'Current Revenue', 'Forecast Revenue Year 1', 'Forecast Revenue Year 2', 'Forecast Revenue Year 3',
        ];
        $result = $this->mapper->match($headers);
        $expected = [
            'currency' => 0,
            'generate_revenue' => 1,
            'average_annual_revenue' => 2,
            'current_operating_income' => 3,
            'forecast_operating_income_y1' => 4,
            'forecast_operating_income_y2' => 5,
            'forecast_operating_income_y3' => 6,
            'current_revenue' => 7,
            'forecast_revenue_y1' => 8,
            'forecast_revenue_y2' => 9,
            'forecast_revenue_y3' => 10,
        ];
        foreach ($expected as $key => $idx) {
            $this->assertSame($idx, $result['mappings'][$key], "header index for {$key}");
        }
    }
}
