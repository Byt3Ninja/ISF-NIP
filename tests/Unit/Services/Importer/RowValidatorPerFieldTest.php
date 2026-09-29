<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\RowValidator;
use App\Services\Importer\ValidationContext;
use Mockery;
use PHPUnit\Framework\TestCase;

class RowValidatorPerFieldTest extends TestCase
{
    private RowValidator $v;

    protected function setUp(): void
    {
        parent::setUp();
        $ctx = Mockery::mock(ValidationContext::class);
        $ctx->shouldReceive('countryIdFor')->andReturnUsing(fn($n) => in_array(strtolower(trim($n)), ['egypt', 'saudi arabia', 'uae']) ? 1 : null);
        $ctx->shouldReceive('stateIdFor')->andReturnUsing(fn($cid, $s) => strtolower(trim($s)) === 'cairo' ? 10 : null);
        $ctx->shouldReceive('isKnownCurrency')->andReturnUsing(fn($c) => in_array(strtoupper(trim($c)), ['USD', 'EGP']));
        $this->v = new RowValidator($ctx);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_required_string_blocks_when_empty(): void
    {
        $this->assertSame('required_string', $this->v->validateField('required_string', '', []));
        $this->assertSame('required_string', $this->v->validateField('required_string', '   ', []));
        $this->assertNull($this->v->validateField('required_string', 'x', []));
    }

    public function test_email_rule(): void
    {
        $this->assertNull($this->v->validateField('email', 'a@b.com', []));
        $this->assertSame('founder_email_invalid', $this->v->validateField('email', 'not-an-email', ['error_code' => 'founder_email_invalid']));
        $this->assertNull($this->v->validateField('email', '', []));
    }

    public function test_phone_rule_accepts_digits_and_plus(): void
    {
        $this->assertNull($this->v->validateField('phone', '1188376532', []));
        $this->assertNull($this->v->validateField('phone', '+201188376532', []));
        $this->assertSame('founder_phone_invalid', $this->v->validateField('phone', 'abc', ['error_code' => 'founder_phone_invalid']));
    }

    public function test_country_code_is_1_to_4_digits(): void
    {
        $this->assertNull($this->v->validateField('country_code', '20', []));
        $this->assertNull($this->v->validateField('country_code', '+20', []));
        $this->assertSame('founder_country_code_invalid', $this->v->validateField('country_code', 'abc', ['error_code' => 'founder_country_code_invalid']));
    }

    public function test_date_mdy_valid_and_invalid(): void
    {
        $this->assertNull($this->v->validateField('date_mdy', '4/1/2026', []));
        $this->assertNull($this->v->validateField('date_mdy', '12/31/2030', []));
        $this->assertSame('program_start_date_invalid', $this->v->validateField('date_mdy', '31/12/2030', ['error_code' => 'program_start_date_invalid']));
        $this->assertSame('program_start_date_invalid', $this->v->validateField('date_mdy', 'not a date', ['error_code' => 'program_start_date_invalid']));
    }

    public function test_date_mdy_past_rejects_future(): void
    {
        $future = '12/31/' . (date('Y') + 5);
        $this->assertSame('founder_dob_not_future', $this->v->validateField('date_mdy_past', $future, ['error_code' => 'founder_dob_not_future']));
        $this->assertNull($this->v->validateField('date_mdy_past', '5/20/1990', []));
    }

    public function test_enum_generic(): void
    {
        $this->assertNull($this->v->validateField('gender_enum', 'Male', ['allowed_values' => ['Male','Female']]));
        $this->assertSame('founder_gender_enum', $this->v->validateField('gender_enum', 'x', ['allowed_values' => ['Male','Female'], 'error_code' => 'founder_gender_enum']));
    }

    public function test_numeric_accepts_int_and_decimal(): void
    {
        $this->assertNull($this->v->validateField('numeric', '1000', []));
        $this->assertNull($this->v->validateField('numeric', '1000.50', []));
        $this->assertSame('numeric_required', $this->v->validateField('numeric', 'abc', ['error_code' => 'numeric_required']));
    }

    public function test_integer_rejects_decimals(): void
    {
        $this->assertNull($this->v->validateField('integer', '12', []));
        $this->assertSame('employees_non_numeric', $this->v->validateField('integer', '12.5', ['error_code' => 'employees_non_numeric']));
    }

    public function test_url_rule(): void
    {
        $this->assertNull($this->v->validateField('url', 'https://acme.example', []));
        $this->assertSame('company_website_invalid', $this->v->validateField('url', 'not a url', ['error_code' => 'company_website_invalid']));
    }

    public function test_yes_no_rule(): void
    {
        $this->assertNull($this->v->validateField('yes_no', 'Yes', []));
        $this->assertNull($this->v->validateField('yes_no', 'No', []));
        $this->assertSame('generate_revenue_enum', $this->v->validateField('yes_no', 'maybe', ['error_code' => 'generate_revenue_enum']));
    }

    public function test_country_name_lookup(): void
    {
        $this->assertNull($this->v->validateField('country_name', 'Egypt', []));
        $this->assertSame('country_required', $this->v->validateField('country_name', 'Atlantis', ['error_code' => 'country_required']));
    }

    public function test_state_name_in_country_requires_country(): void
    {
        $this->assertNull($this->v->validateField('state_name_in_country', 'Cairo', []));
    }

    public function test_currency_code_rule(): void
    {
        $this->assertNull($this->v->validateField('currency_code', 'USD', []));
        $this->assertSame('currency_invalid', $this->v->validateField('currency_code', 'ZZZ', ['error_code' => 'currency_invalid']));
    }

    public function test_target_markets_parses_comma_list(): void
    {
        $this->assertNull($this->v->validateField('target_markets', 'Egypt, Saudi Arabia', []));
        $this->assertSame('target_markets_invalid', $this->v->validateField('target_markets', 'Atlantis, Egypt', ['error_code' => 'target_markets_invalid']));
    }

    public function test_image_url_uses_url_check(): void
    {
        $this->assertNull($this->v->validateField('image_url', 'https://x.example/logo.png', []));
        $this->assertSame('company_logo_invalid', $this->v->validateField('image_url', 'nope', ['error_code' => 'company_logo_invalid']));
    }
}
