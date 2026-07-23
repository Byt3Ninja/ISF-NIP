<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Company;
use App\StartupCustomerGrowth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartupCustomerGrowthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    private function createUserAndCompany()
    {
        $company = new Company();
        $company->name_en = 'Test Company';
        $company->name_ar = 'Test Company AR';
        $company->address_en = '123 Test St';
        $company->address_ar = '123 Test St AR';
        $company->save();

        $user = new User();
        $user->first_name_en = 'Test';
        $user->last_name_en = 'User';
        $user->first_name_ar = 'Test';
        $user->last_name_ar = 'User';
        $user->email = 'test' . rand(1000, 9999) . '@example.com';
        $user->password = bcrypt('password');
        $user->email_verified_at = now();
        $user->company_id = $company->id;
        $user->HaveCompany = 1;
        $user->save();

        return [$user, $company];
    }

    /**
     * Test validation rules (required fields and integers)
     */
    public function test_validation_rules_require_valid_fields()
    {
        list($user, $company) = $this->createUserAndCompany();
        $this->actingAs($user, 'web');

        // Test missing year and month
        $response = $this->post('/dashboard/startupGrowth/add', [
            'company_id' => $company->id,
            'add_growth_total_customers' => 100,
            'add_growth_new_customers' => 10,
            'add_growth_segment' => 'mixed',
        ]);
        $response->assertSessionHasErrors(['add_growth_year', 'add_growth_month']);

        // Test non-integer customers validation
        $response = $this->post('/dashboard/startupGrowth/add', [
            'company_id' => $company->id,
            'add_growth_year' => date('Y'),
            'add_growth_month' => 6,
            'add_growth_total_customers' => 100.5,
            'add_growth_new_customers' => 10.2,
            'add_growth_segment' => 'mixed',
        ]);
        $response->assertSessionHasErrors(['add_growth_total_customers', 'add_growth_new_customers']);
        $errors = session('errors')->get('add_growth_total_customers');
        $this->assertEquals(__('startup.please_enter_a_valid_whole_number'), $errors[0]);
    }

    /**
     * Test duplicate prevention
     */
    public function test_prevents_duplicate_records_for_same_period()
    {
        list($user, $company) = $this->createUserAndCompany();
        $this->actingAs($user, 'web');

        // Insert first record
        $record = new StartupCustomerGrowth();
        $record->company_id = $company->id;
        $record->year = date('Y');
        $record->month = 6;
        $record->total_customers = 100;
        $record->new_customers = 10;
        $record->segment = 'mixed';
        $record->save();

        // Attempt to insert duplicate
        $response = $this->post('/dashboard/startupGrowth/add', [
            'company_id' => $company->id,
            'add_growth_year' => date('Y'),
            'add_growth_month' => 6,
            'add_growth_total_customers' => 150,
            'add_growth_new_customers' => 20,
            'add_growth_segment' => 'mixed',
        ]);

        $response->assertSessionHas('error', __('startup.customer_growth_already_exist'));
    }

    /**
     * Test saving record successfully
     */
    public function test_saves_record_successfully()
    {
        list($user, $company) = $this->createUserAndCompany();
        $this->actingAs($user, 'web');

        // Use a large integer to make sure it saves correctly
        $largeNumber = 99999999999; 

        $response = $this->post('/dashboard/startupGrowth/add', [
            'company_id' => $company->id,
            'add_growth_year' => date('Y'),
            'add_growth_month' => 6,
            'add_growth_total_customers' => $largeNumber,
            'add_growth_new_customers' => 200,
            'add_growth_segment' => 'mixed',
            'add_growth_notes' => 'Some notes here',
        ]);

        $response->assertSessionHas('success', __('startup.customer_growth_record_added'));

        $this->assertDatabaseHas('startup_customer_growth', [
            'company_id' => $company->id,
            'year' => date('Y'),
            'month' => 6,
            'total_customers' => $largeNumber,
            'new_customers' => 200,
            'segment' => 'mixed',
            'notes' => 'Some notes here',
        ]);
    }
}
