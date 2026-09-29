<?php

namespace Tests\Feature\Importer;

use App\User;
use App\Services\Importer\ImporterSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('importer');
    }

    private function makeUser(): User
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        return $user;
    }

    private function fixture(string $name): UploadedFile
    {
        $path = __DIR__ . '/../../Fixtures/Importer/' . $name;
        return new UploadedFile($path, $name, mime_content_type($path) ?: 'text/csv', null, true);
    }

    private function createSession(User $user): string
    {
        $resp = $this->actingAs($user)->postJson('/dashboard/importer/sessions');
        return $resp->json('data.session_id');
    }

    public function test_valid_upload_creates_file_on_disk_and_returns_headers(): void
    {
        $user = $this->makeUser();
        $uuid = $this->createSession($user);

        $resp = $this->actingAs($user)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $this->fixture('valid_minimal.csv')],
            ['Accept' => 'application/json']
        );

        $resp->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.status', ImporterSession::STATUS_UPLOADED)
             ->assertJsonPath('data.row_count', 1);

        $headers = $resp->json('data.headers');
        $this->assertIsArray($headers);
        $this->assertSame('Program Name', $headers[0]);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        Storage::disk('importer')->assertExists($session->file_path);
    }

    public function test_invalid_csv_upload_returns_422_and_no_file_saved(): void
    {
        $user = $this->makeUser();
        $uuid = $this->createSession($user);

        $resp = $this->actingAs($user)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $this->fixture('invalid_duplicate_headers.csv')],
            ['Accept' => 'application/json']
        );

        $resp->assertStatus(422)
             ->assertJsonPath('success', false);

        $codes = array_column($resp->json('errors'), 'code');
        $this->assertContains('duplicate_headers', $codes);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        $this->assertNull($session->file_path, 'no file should be persisted on validation failure');
    }

    public function test_upload_to_another_users_session_returns_404(): void
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        $uuid = $this->createSession($owner);

        $resp = $this->actingAs($intruder)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $this->fixture('valid_minimal.csv')],
            ['Accept' => 'application/json']
        );

        $resp->assertStatus(404);
    }

    public function test_delete_file_clears_storage_but_keeps_session(): void
    {
        $user = $this->makeUser();
        $uuid = $this->createSession($user);

        $this->actingAs($user)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $this->fixture('valid_minimal.csv')],
            ['Accept' => 'application/json']
        )->assertStatus(200);

        $resp = $this->actingAs($user)->deleteJson("/dashboard/importer/sessions/{$uuid}/file");
        $resp->assertStatus(200)->assertJsonPath('success', true);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        $this->assertNull($session->file_path);
        $this->assertSame(ImporterSession::STATUS_UPLOADED, $session->status, 'session stays active after file delete');
    }

    public function test_show_session_returns_current_state(): void
    {
        $user = $this->makeUser();
        $uuid = $this->createSession($user);
        $this->actingAs($user)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $this->fixture('valid_minimal.csv')],
            ['Accept' => 'application/json']
        );

        $resp = $this->actingAs($user)->getJson("/dashboard/importer/sessions/{$uuid}");
        $resp->assertStatus(200)
             ->assertJsonPath('data.status', ImporterSession::STATUS_UPLOADED)
             ->assertJsonPath('data.row_count', 1);
    }

    public function test_discard_session_cleans_file_and_transitions(): void
    {
        $user = $this->makeUser();
        $uuid = $this->createSession($user);
        $this->actingAs($user)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $this->fixture('valid_minimal.csv')],
            ['Accept' => 'application/json']
        );

        $resp = $this->actingAs($user)->deleteJson("/dashboard/importer/sessions/{$uuid}");
        $resp->assertStatus(200);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        $this->assertSame(ImporterSession::STATUS_DISCARDED, $session->status);
        $this->assertNull($session->file_path);
    }
}
