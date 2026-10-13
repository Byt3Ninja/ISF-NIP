<?php

namespace Tests\Feature\Importer;

use App\Company;
use App\Cycle;
use App\Organization;
use App\Services\Importer\ImporterSession;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConflictDetectionEndpointTest extends TestCase
{
    use DatabaseTransactions;

    private Organization $org;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('importer');

        $this->admin = User::factory()->create();
        $this->admin->allotRole('Admin');

        $this->org = Organization::create([
            'name_en' => 'Test Org PR5',
            'name_ar' => 'منظمة اختبار',
            'user_id' => $this->admin->id,
        ]);

        // Seed an existing program in the org matching row 4's name+type but with different dates
        Cycle::forceCreate([
            'title'           => 'OldProgram',
            'type'            => 'General_Program',
            'start'           => '2024-01-01',
            'end'             => '2024-06-01',
            'organization_id' => $this->org->id,
        ]);

        // Seed an existing enrolled founder matching row 2's email
        $cycleEnrolled = Cycle::forceCreate([
            'title'           => 'Other Program',
            'type'            => 'General_Program',
            'start'           => '2024-01-01',
            'end'             => '2024-06-01',
            'organization_id' => $this->org->id,
        ]);

        $company = Company::create([
            'name_en'    => 'ExistingCo',
            'name_ar'    => 'شركة قائمة',
            'address_en' => 'address',
            'address_ar' => 'عنوان',
            'phone'      => '0000000000',
            'email'      => 'existingco@example.com',
            'cycle'      => $cycleEnrolled->id,
        ]);

        User::factory()->create([
            'email'         => 'alice.duplicate@example.com',
            'first_name_en' => 'Alice',
            'last_name_en'  => 'Duplicate',
            'phone'         => '1100000099',
            'company_id'    => $company->id,
        ]);

        // Seed a user with email matching row 3 but a different name
        User::factory()->create([
            'email'         => 'bob.exists@example.com',
            'first_name_en' => 'Robert',
            'last_name_en'  => 'Original',
        ]);
    }

    private function uploadAndValidate(): string
    {
        $create = $this->actingAs($this->admin)->postJson('/dashboard/importer/sessions');
        $uuid   = $create->json('data.session_id');
        $path   = __DIR__ . '/../../Fixtures/Importer/conflicts_seeded.csv';
        $file   = new UploadedFile($path, 'conflicts_seeded.csv', 'text/csv', null, true);
        $this->actingAs($this->admin)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $file],
            ['Accept' => 'application/json']
        )->assertStatus(200);

        $maps = $this->actingAs($this->admin)
            ->postJson("/dashboard/importer/sessions/{$uuid}/automap")
            ->json('data.mappings');
        $this->actingAs($this->admin)
            ->postJson("/dashboard/importer/sessions/{$uuid}/validate", ['mappings' => $maps])
            ->assertJsonPath('data.valid', true);

        return $uuid;
    }

    public function test_returns_warnings_for_seeded_conflicts(): void
    {
        $uuid = $this->uploadAndValidate();
        $resp = $this->actingAs($this->admin)->postJson(
            "/dashboard/importer/sessions/{$uuid}/conflicts",
            ['organization_id' => $this->org->id]
        );

        $resp->assertStatus(200)->assertJsonPath('success', true);

        $types = array_column($resp->json('data.warnings'), 'type');
        $this->assertContains('founder_email_already_enrolled', $types);
        $this->assertContains('founder_email_exists_different_name', $types);
        $this->assertContains('program_exists_different_dates', $types);
    }

    public function test_org_scoped_warnings_disappear_for_other_org(): void
    {
        $other = Organization::create([
            'name_en' => 'Other Org PR5',
            'name_ar' => 'منظمة أخرى',
            'user_id' => $this->admin->id,
        ]);
        $uuid  = $this->uploadAndValidate();
        $resp  = $this->actingAs($this->admin)->postJson(
            "/dashboard/importer/sessions/{$uuid}/conflicts",
            ['organization_id' => $other->id]
        );
        $resp->assertStatus(200)->assertJsonPath('success', true);

        // Org-scoped warnings (enrollment + program) must disappear for a different org.
        $types = array_column($resp->json('data.warnings'), 'type');
        $this->assertNotContains('founder_email_already_enrolled', $types);
        $this->assertNotContains('founder_phone_already_enrolled', $types);
        $this->assertNotContains('program_exists_different_dates', $types);
    }

    public function test_accept_flag_transitions_session(): void
    {
        $uuid = $this->uploadAndValidate();

        $this->actingAs($this->admin)->postJson(
            "/dashboard/importer/sessions/{$uuid}/conflicts",
            ['organization_id' => $this->org->id, 'accept' => true]
        )->assertStatus(200);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        $this->assertSame(ImporterSession::STATUS_CONFLICTS_REVIEWED, $session->status);
        $this->assertSame($this->org->id, $session->organization_id);
        $this->assertNotNull($session->conflict_warnings);
    }

    public function test_requires_validated_status(): void
    {
        $create = $this->actingAs($this->admin)->postJson('/dashboard/importer/sessions');
        $uuid   = $create->json('data.session_id');

        // No upload / no validate: still in 'uploaded' status and missing row data
        $resp = $this->actingAs($this->admin)->postJson(
            "/dashboard/importer/sessions/{$uuid}/conflicts",
            ['organization_id' => $this->org->id]
        );
        $resp->assertStatus(409);
    }
}
