<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;

class MilestoneSubmissionTest extends TestCase
{
    // use RefreshDatabase; // Be careful with this on existing DBs

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    /*
     * Test PPTX upload is allowed
     */
    public function test_milestone_allows_pptx_upload()
    {
        Storage::fake('public');

        // Create a user and company manually
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

        $this->actingAs($user, 'web');

        $file = UploadedFile::fake()->create('presentation.pptx', 1000, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $response = $this->post(route('company.profile_milestones_add', $company->id), [
            'name' => 'Test Milestone',
            'description' => 'Description here',
            'completion_percentage' => 50,
            'category' => 'General',
            'status' => 'not_started',
            'date' => now()->format('Y-m-d'),
            'visibility' => 'public',
            'documents' => [$file],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /*
     * Test TXT upload is NOT allowed
     */
    public function test_milestone_rejects_txt_upload()
    {
        Storage::fake('public');

        // Create a user and company manually
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

        $this->actingAs($user, 'web');

        $file = UploadedFile::fake()->create('document.txt', 1000, 'text/plain');

        $response = $this->post(route('company.profile_milestones_add', $company->id), [
            'name' => 'Test Milestone',
            'description' => 'Description here',
            'completion_percentage' => 50,
            'category' => 'General',
            'status' => 'not_started',
            'date' => now()->format('Y-m-d'),
            'visibility' => 'public',
            'documents' => [$file],
        ]);

        $response->assertStatus(422);
        // The error response structure for documents.* validation might be specific
        // It usually returns errors key
        $response->assertJsonValidationErrors(['documents.0']);
    }
}
