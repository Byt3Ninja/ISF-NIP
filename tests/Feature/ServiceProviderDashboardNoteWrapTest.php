<?php

namespace Tests\Feature;

use App\User;
use App\serviceProviders;
use App\Service;
use App\ServicesRequests;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ServiceProviderDashboardNoteWrapTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\LoginSecurityMiddleware::class);

        \DB::table('settings')->insertOrIgnore([
            'id' => 1,
            'type' => '1',
            'title' => 'Test Platform',
            'ar_title' => 'منصة اختبار',
            'assessment' => 1,
        ]);
    }

    public function test_service_provider_dashboard_contains_note_wrapping_styles(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['ableTo'])
            ->getMock();
        $user->method('ableTo')->willReturn(true);

        // Share errors globally to avoid Undefined variable $errors
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        $this->actingAs($user);

        $view = $this->view('admin.service-provider.dashboard', [
            'summary' => [
                'totalServices' => 0,
                'activeServices' => 0,
                'requests30Days' => 0,
                'pendingRequests' => 0,
                'medianResponseTime' => null,
                'completionRate' => null,
            ],
            'totalRequests' => 0,
            'chartServices' => [],
            'topService'    => null,
            'rvLabels'      => '[]',
            'rvData'        => '[]',
            'sdColors'      => '[]',
            'sdDimColors'   => '[]',
            'sdData'        => '[]',
            'sdLabels'      => '[]',
            'services' => collect(),
            'inboxRows' => [],
            'activityEntries' => [],
            'canQuickAction' => true,
            'canBulkAction' => true,
            'approvalStatus' => 'accepted',
            'rejectionReason' => '',
            'permissions' => ['dashboard.widgets_tables_service_provider_requests_inbox_view', 'dashboard.widgets_tables_service_provider_recent_activity_view'],
            '_user' => $user,
        ]);

        // Assert Note table header contains max-width styling
        $view->assertSee('style="max-width: 250px;"', false);

        // Assert Note table cell in JS renderInboxRows function contains max-width and break-word styling
        $view->assertSee('style="max-width: 250px; min-width: 150px; white-space: normal; word-break: break-word;"', false);

        // Assert SweetAlert dialog options for rejection do not contain Deny/No button and use Bootstrap buttons
        $view->assertSee('showDenyButton: false', false);
        $view->assertSee("confirmButton: 'btn btn-danger ms-3'", false);
        $view->assertSee("cancelButton: 'btn btn-label-secondary'", false);
        $view->assertSee('buttonsStyling: false', false);
        $view->assertSee('reverseButtons: true', false);
        $view->assertSee('cancelButtonText: LABELS.cancel', false);
        $view->assertSee('cancel:', false);

        // Assert Show Request Details button/action mapping and HTML class in JavaScript rendering
        $view->assertSee('showDetails:', false);
        $view->assertSee('btn-details', false);

        // Assert charts have dynamic localization checks
        $view->assertSee("label: lang === 'ar' ? 'الطلبات' : 'Requests'", false);
        $view->assertSee("lang === 'ar' ? ' طلبات' : ' requests'", false);
    }

    public function test_service_provider_action_adds_recent_activity(): void
    {
        // 1. Create a service provider user
        $user = User::factory()->create([
            'email' => 'provider@example.com',
        ]);

        $role = \App\Role::firstOrCreate(['name' => 'serviceProvider'], ['default_for' => 'serviceProvider']);
        $p1 = \App\Permission::firstOrCreate(['name' => 'service_requests.take_action'], ['description' => 'Take action']);
        $p2 = \App\Permission::firstOrCreate(['name' => 'service_provider_dashboard.activity'], ['description' => 'View activity']);
        $role->permissions()->syncWithoutDetaching([$p1->id, $p2->id]);
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $user->allotRole('serviceProvider');

        // 2. Create the service provider profile record
        $provider = serviceProviders::create([
            'company_name' => 'Test Provider Co',
            'contact_name' => 'Provider Contact',
            'email' => 'provider@example.com',
            'status' => true,
            'user_id' => $user->id,
            'is_verified' => true,
        ]);

        \DB::table('service_provider_registration')->insert([
            'user_id' => $user->id,
            'status' => 1,
            'company_name' => 'Test Provider Co',
            'email' => 'provider@example.com',
            'phone' => '12345678',
            'contact_name' => 'Provider Contact',
            'country_code' => '966',
        ]);

        \DB::table('service_categories')->insertOrIgnore([
            'id' => 1,
            'name_en' => 'Test Category',
            'name_ar' => 'تصنيف تجريبي',
        ]);

        // 3. Create a service under the provider
        $service = Service::create([
            'service_provider_id' => $provider->id,
            'service_category_id' => 1,
            'name_en' => 'Test Service English',
            'name_ar' => 'خدمة تجريبية',
            'status' => true,
        ]);

        // 4. Create a founder user
        $founder = User::factory()->create([
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
        ]);

        // 5. Create a service request
        $serviceRequest = ServicesRequests::create([
            'service_id' => $service->id,
            'user_id' => $founder->id,
            'comment' => 'Please help me',
        ]);

        // 6. Act as the service provider and take action (approve)
        $this->actingAs($user, 'web');

        // Execute action POST request
        $response = $this->postJson('/dashboard/requests/take_action', [
            'id' => $serviceRequest->id,
            'stat' => 'approve',
        ]);

        $response->assertStatus(200);

        // 7. Verify an audit log entry was created
        $this->assertDatabaseHas('audit_logs', [
            'subject_id' => $serviceRequest->id,
            'subject_type' => "App\\ServicesRequests#{$serviceRequest->id}",
            'description' => 'audit:updated',
        ]);

        // 8. Fetch recent activity AJAX endpoint
        $activityResponse = $this->getJson('/dashboard/service-provider/activity');

        $activityResponse->assertStatus(200);
        $activityResponse->assertJsonFragment([
            'type' => 'accepted',
            'text' => 'You accepted a request for Test Service English',
        ]);
    }
}
