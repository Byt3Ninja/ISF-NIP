<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\RowValidator;
use App\Services\Importer\ValidationContext;
use Mockery;
use PHPUnit\Framework\TestCase;

class RowValidatorCrossFieldTest extends TestCase
{
    private RowValidator $v;

    protected function setUp(): void
    {
        parent::setUp();
        $ctx = Mockery::mock(ValidationContext::class);
        $ctx->shouldReceive('countryIdFor')->andReturnUsing(fn($n) => strtolower(trim($n)) === 'egypt' ? 1 : null);
        $ctx->shouldReceive('stateIdFor')->andReturnUsing(fn($cid, $s) => ($cid === 1 && strtolower(trim($s)) === 'cairo') ? 10 : null);
        $ctx->shouldReceive('isKnownCurrency')->andReturnUsing(fn($c) => strtoupper($c) === 'USD');
        $this->v = new RowValidator($ctx);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_program_dates_order_fails_when_start_after_end(): void
    {
        $errors = $this->v->validateCrossFields([
            'program_start_date' => '7/1/2026',
            'program_end_date'   => '6/1/2026',
        ]);
        $codes = array_column($errors, 'code');
        $this->assertContains('program_dates_order', $codes);
    }

    public function test_program_dates_order_passes_when_equal_or_before(): void
    {
        $this->assertEmpty($this->v->validateCrossFields([
            'program_start_date' => '6/1/2026',
            'program_end_date'   => '6/1/2026',
        ]));
    }

    public function test_cofounder_requires_name_when_email_present(): void
    {
        $errors = $this->v->validateCrossFields(['cofounder_email' => 'c@d.com', 'cofounder_name' => '']);
        $this->assertContains('cofounder_name_required', array_column($errors, 'code'));
    }

    public function test_cofounder_requires_email_when_phone_present(): void
    {
        $errors = $this->v->validateCrossFields(['cofounder_phone' => '555', 'cofounder_email' => '']);
        $this->assertContains('cofounder_email_required', array_column($errors, 'code'));
    }

    public function test_cofounder_email_distinct(): void
    {
        $errors = $this->v->validateCrossFields(['founder_email' => 'a@b.com', 'cofounder_email' => 'a@b.com', 'cofounder_name' => 'X']);
        $this->assertContains('cofounder_email_distinct', array_column($errors, 'code'));
    }

    public function test_state_belongs_to_country(): void
    {
        $errors = $this->v->validateCrossFields(['country' => 'Egypt', 'governorate' => 'Riyadh']);
        $this->assertContains('state_belongs_to_country', array_column($errors, 'code'));

        $this->assertEmpty($this->v->validateCrossFields(['country' => 'Egypt', 'governorate' => 'Cairo']));
    }

    public function test_prev_incubator_requires_flag(): void
    {
        $errors = $this->v->validateCrossFields(['is_previously_incubated' => 'No', 'previous_incubator' => 'Acme']);
        $this->assertContains('prev_incubator_requires_flag', array_column($errors, 'code'));
    }

    public function test_male_gt_total(): void
    {
        $errors = $this->v->validateCrossFields(['total_employees' => '10', 'male_employees' => '11']);
        $this->assertContains('male_employees_gt_total', array_column($errors, 'code'));
    }

    public function test_female_gt_total(): void
    {
        $errors = $this->v->validateCrossFields(['total_employees' => '10', 'female_employees' => '11']);
        $this->assertContains('female_employees_gt_total', array_column($errors, 'code'));
    }

    public function test_sum_gt_total(): void
    {
        $errors = $this->v->validateCrossFields(['total_employees' => '10', 'male_employees' => '6', 'female_employees' => '5']);
        $this->assertContains('male_plus_female_gt_total', array_column($errors, 'code'));
    }

    public function test_financials_require_currency(): void
    {
        $errors = $this->v->validateCrossFields(['current_revenue' => '1000', 'currency' => '']);
        $this->assertContains('currency_required', array_column($errors, 'code'));

        $this->assertEmpty($this->v->validateCrossFields(['current_revenue' => '1000', 'currency' => 'USD']));
    }

    public function test_founder_dob_not_future(): void
    {
        $future = '12/31/' . (date('Y') + 5);
        $errors = $this->v->validateCrossFields(['founder_date_of_birth' => $future]);
        $this->assertContains('founder_dob_not_future', array_column($errors, 'code'));
    }
}
