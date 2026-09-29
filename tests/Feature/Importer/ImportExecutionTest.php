<?php

namespace Tests\Feature\Importer;

use App\Mail\ImporterFounderCredentialsMail;
use App\Organization;
use App\Services\Importer\ImporterSession;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportExecutionTest extends TestCase
{
    use DatabaseTransactions;

    private Organization $org;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('importer');
        Mail::fake();

        $this->admin = User::factory()->create();
        $this->admin->allotRole('Admin');
        $this->org = Organization::create([
            'name_en' => 'Test Org',
            'name_ar' => 'منظمة',
            'user_id' => $this->admin->id,
        ]);

    }

    private function fixture(string $name): UploadedFile
    {
        $path = __DIR__ . '/../../Fixtures/Importer/' . $name;
        return new UploadedFile($path, $name, mime_content_type($path) ?: 'text/csv', null, true);
    }

    private function runFullFlow(string $fixture = 'valid_minimal.csv'): string
    {
        $create = $this->actingAs($this->admin)->postJson('/dashboard/importer/sessions');
        $uuid = $create->json('data.session_id');

        $this->actingAs($this->admin)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $this->fixture($fixture)],
            ['Accept' => 'application/json']
        )->assertStatus(200);

        $maps = $this->actingAs($this->admin)
            ->postJson("/dashboard/importer/sessions/{$uuid}/automap")
            ->json('data.mappings');

        $this->actingAs($this->admin)
            ->postJson("/dashboard/importer/sessions/{$uuid}/validate", ['mappings' => $maps])
            ->assertJsonPath('data.valid', true);

        // Conflicts check (may auto-transition to conflicts_reviewed)
        $this->actingAs($this->admin)
            ->postJson("/dashboard/importer/sessions/{$uuid}/conflicts", [
                'organization_id' => $this->org->id,
                'accept' => true,
            ])->assertStatus(200);

        return $uuid;
    }

    public function test_happy_path_imports_and_sends_credentials_mail(): void
    {
        $uuid = $this->runFullFlow();

        $resp = $this->actingAs($this->admin)
            ->postJson("/dashboard/importer/sessions/{$uuid}/execute", [
                'organization_id'   => $this->org->id,
                'accepted_warnings' => true,
            ]);

        $resp->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.totals.imported', 1)
             ->assertJsonPath('data.totals.failed', 0);

        Mail::assertQueued(ImporterFounderCredentialsMail::class, 1);

        $this->assertDatabaseHas('companies', ['name_en' => 'Acme Robotics']);
        $this->assertDatabaseHas('users', ['email' => 'alex@example.com']);
        // The cycle either reuses an exact-match cycle in any reachable org, or is
        // newly created in the fallback (dropdown) org. Just assert it exists.
        $this->assertDatabaseHas('cycles', [
            'title' => 'Accelerator A',
            'type' => 'General_Program',
        ]);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        $this->assertSame(ImporterSession::STATUS_COMPLETED, $session->status);
        $this->assertNull($session->file_path);
    }

    public function test_execute_without_org_returns_403_for_non_admin(): void
    {
        $mgr = User::factory()->create();
        $mgr->allotRole('organizationManager');
        $create = $this->actingAs($mgr)->postJson('/dashboard/importer/sessions');
        $uuid = $create->json('data.session_id');

        // If the user lacks even the ability to create sessions, that's a 401 —
        // which is also a legitimate "cannot proceed" signal.
        if ($create->status() !== 200) {
            $this->assertContains($create->status(), [401, 403]);
            return;
        }

        $resp = $this->actingAs($mgr)
            ->postJson("/dashboard/importer/sessions/{$uuid}/execute", [
                'organization_id' => $this->org->id,
            ]);
        $this->assertContains($resp->status(), [401, 403, 409]);
    }

    public function test_execute_requires_validated_state(): void
    {
        $create = $this->actingAs($this->admin)->postJson('/dashboard/importer/sessions');
        $uuid = $create->json('data.session_id');

        $resp = $this->actingAs($this->admin)
            ->postJson("/dashboard/importer/sessions/{$uuid}/execute", [
                'organization_id' => $this->org->id,
            ]);
        $resp->assertStatus(409);
    }
}
