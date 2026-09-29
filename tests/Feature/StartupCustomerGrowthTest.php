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

    /**
     * Test updating past record and recalculating growth percentages
     */
    public function test_updates_record_successfully_and_recalculates_growth()
    {
        list($user, $company) = $this->createUserAndCompany();
        $this->actingAs($user, 'web');

        $record1 = new StartupCustomerGrowth();
        $record1->company_id = $company->id;
        $record1->year = 2025;
        $record1->month = 1;
        $record1->total_customers = 100;
        $record1->new_customers = 10;
        $record1->segment = 'b2c';
        $record1->save();

        $record2 = new StartupCustomerGrowth();
        $record2->company_id = $company->id;
        $record2->year = 2025;
        $record2->month = 2;
        $record2->total_customers = 130;
        $record2->new_customers = 30;
        $record2->segment = 'b2c';
        $record2->save();

        // Initial records calculation: record2 MoM should be +30%
        $initialData = StartupCustomerGrowth::records($company->id);
        $this->assertEquals('+30%', $initialData['records'][1]->MoM);

        // Update record1 total_customers from 100 to 110
        $response = $this->post('/dashboard/startupGrowth/update', [
            'record_id' => $record1->id,
            'company_id' => $company->id,
            'add_growth_year' => 2025,
            'add_growth_month' => 1,
            'add_growth_total_customers' => 110,
            'add_growth_new_customers' => 10,
            'add_growth_segment' => 'b2c',
            'add_growth_notes' => 'Updated notes',
        ]);

        $response->assertSessionHas('success', __('startup.customer_growth_record_updated'));

        // Recalculated MoM for record2 should now be (130 - 110) / 110 * 100 = +18%
        $updatedData = StartupCustomerGrowth::records($company->id);
        $this->assertEquals(110, $updatedData['records'][0]->total);
        $this->assertEquals('+18%', $updatedData['records'][1]->MoM);
    }

    /**
     * Test deleting past record and recalculating growth percentages
     */
    public function test_deletes_record_successfully_and_recalculates_growth()
    {
        list($user, $company) = $this->createUserAndCompany();
        $this->actingAs($user, 'web');

        $record1 = new StartupCustomerGrowth();
        $record1->company_id = $company->id;
        $record1->year = 2025;
        $record1->month = 1;
        $record1->total_customers = 100;
        $record1->new_customers = 10;
        $record1->segment = 'b2b';
        $record1->save();

        $record2 = new StartupCustomerGrowth();
        $record2->company_id = $company->id;
        $record2->year = 2025;
        $record2->month = 2;
        $record2->total_customers = 120;
        $record2->new_customers = 20;
        $record2->segment = 'b2b';
        $record2->save();

        $response = $this->delete('/dashboard/startupGrowth/delete/' . $record1->id);
        $response->assertSessionHas('msg', 'customer_growth_record_deleted');

        $this->assertDatabaseMissing('startup_customer_growth', ['id' => $record1->id]);

        // After deleting record1, record2 is now the first record, so MoM should be 'Not available'
        $remainingData = StartupCustomerGrowth::records($company->id);
        $this->assertCount(1, $remainingData['records']);
        $this->assertEquals(__('startup.growth_not_available'), $remainingData['records'][0]->MoM);
    }

    /**
     * Test adding and viewing yearly entries (month 0)
     */
    public function test_supports_yearly_entries()
    {
        list($user, $company) = $this->createUserAndCompany();
        $this->actingAs($user, 'web');

        $response = $this->post('/dashboard/startupGrowth/add', [
            'company_id' => $company->id,
            'add_growth_year' => 2024,
            'add_growth_month' => 0,
            'add_growth_total_customers' => 500,
            'add_growth_new_customers' => 100,
            'add_growth_segment' => 'enterprise',
        ]);

        $response->assertSessionHas('success', __('startup.customer_growth_record_added'));

        $this->assertDatabaseHas('startup_customer_growth', [
            'company_id' => $company->id,
            'year' => 2024,
            'month' => 0,
            'total_customers' => 500,
            'segment' => 'enterprise',
        ]);
    }

    /**
     * Test exporting CSV with year and segment filters
     */
    public function test_exports_csv_with_filters()
    {
        list($user, $company) = $this->createUserAndCompany();
        $this->actingAs($user, 'web');

        $record1 = new StartupCustomerGrowth();
        $record1->company_id = $company->id;
        $record1->year = 2024;
        $record1->month = 5;
        $record1->total_customers = 200;
        $record1->new_customers = 50;
        $record1->segment = 'b2c';
        $record1->save();

        $record2 = new StartupCustomerGrowth();
        $record2->company_id = $company->id;
        $record2->year = 2025;
        $record2->month = 6;
        $record2->total_customers = 400;
        $record2->new_customers = 100;
        $record2->segment = 'enterprise';
        $record2->save();

        // Export with year filter 2025
        $response = $this->get('/dashboard/startupGrowth/export/' . $company->id . '?year=2025');
        $response->assertStatus(200);
        
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('2025', $content);
        $this->assertStringNotContainsString('2024', $content);

        // Export with segment filter enterprise
        $response2 = $this->get('/dashboard/startupGrowth/export/' . $company->id . '?segment=b2c');
        $response2->assertStatus(200);

        ob_start();
        $response2->sendContent();
        $content2 = ob_get_clean();

        $this->assertStringContainsString('B2C', $content2);
        $this->assertStringNotContainsString('ENTERPRISE', $content2);
    }
}
