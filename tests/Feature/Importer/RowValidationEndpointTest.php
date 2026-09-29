<?php

namespace Tests\Feature\Importer;

use App\Services\Importer\ImporterSession;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RowValidationEndpointTest extends TestCase
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

    private function upload(User $user, string $fixture): string
    {
        $create = $this->actingAs($user)->postJson('/dashboard/importer/sessions');
        $uuid   = $create->json('data.session_id');
        $path   = __DIR__ . '/../../Fixtures/Importer/' . $fixture;
        $file   = new UploadedFile($path, $fixture, 'text/csv', null, true);
        $this->actingAs($user)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $file],
            ['Accept' => 'application/json']
        )->assertStatus(200);
        return $uuid;
    }

    private function automapMappings(User $user, string $uuid): array
    {
        $resp = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/automap");
        return $resp->json('data.mappings');
    }

    public function test_valid_rows_pass(): void
    {
        $user = $this->makeUser();
        $uuid = $this->upload($user, 'valid_minimal.csv');
        $maps = $this->automapMappings($user, $uuid);

        $resp = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/validate", [
            'mappings' => $maps,
        ]);

        $resp->assertStatus(200)
             ->assertJsonPath('data.valid', true)
             ->assertJsonPath('data.totals.blocking', 0);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        $this->assertSame(ImporterSession::STATUS_VALIDATED, $session->status);
        $this->assertSame($maps, $session->mappings);
    }

    public function test_mixed_errors_file_returns_multiple_row_errors(): void
    {
        $user = $this->makeUser();
        $uuid = $this->upload($user, 'row_errors_mixed.csv');
        $maps = $this->automapMappings($user, $uuid);

        $resp = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/validate", [
            'mappings' => $maps,
        ]);

        $resp->assertStatus(200)->assertJsonPath('data.valid', false);

        $codes = array_column($resp->json('data.row_errors'), 'code');
        $this->assertContains('program_name_required', $codes);
        $this->assertContains('program_type_enum', $codes);
        $this->assertContains('program_start_date_invalid', $codes);
        $this->assertContains('founder_email_invalid', $codes);
        $this->assertContains('program_dates_order', $codes);
        $this->assertContains('founder_gender_enum', $codes);
        $this->assertContains('founder_phone_invalid', $codes);
        $this->assertContains('founder_country_code_invalid', $codes);
        $this->assertContains('startup_name_required', $codes);
        $this->assertContains('country_required', $codes);

        $session = ImporterSession::where('uuid', $uuid)->firstOrFail();
        $this->assertSame(ImporterSession::STATUS_UPLOADED, $session->status, 'failed validation must NOT transition session');
        $this->assertNull($session->mappings, 'mappings are not persisted on validation failure');
    }

    public function test_missing_required_mapping_blocks(): void
    {
        $user = $this->makeUser();
        $uuid = $this->upload($user, 'valid_minimal.csv');
        $maps = $this->automapMappings($user, $uuid);
        $maps['founder_email'] = null; // unmap a required field

        $resp = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/validate", [
            'mappings' => $maps,
        ]);

        $resp->assertStatus(200)->assertJsonPath('data.valid', false);
        $codes = array_column($resp->json('data.row_errors'), 'code');
        $this->assertContains('field_required', $codes);
    }
}
