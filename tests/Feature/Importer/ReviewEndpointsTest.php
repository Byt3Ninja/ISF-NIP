<?php

namespace Tests\Feature\Importer;

use App\Organization;
use App\Services\Importer\ImporterSession;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('importer');
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        return $user;
    }

    private function completeUpToStep3(User $user): string
    {
        $create = $this->actingAs($user)->postJson('/dashboard/importer/sessions');
        $uuid   = $create->json('data.session_id');
        $path   = __DIR__ . '/../../Fixtures/Importer/valid_minimal.csv';
        $file   = new UploadedFile($path, 'valid_minimal.csv', 'text/csv', null, true);
        $this->actingAs($user)->post("/dashboard/importer/sessions/{$uuid}/file", ['file' => $file], ['Accept' => 'application/json'])->assertStatus(200);

        $maps = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/automap")->json('data.mappings');
        $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/validate", ['mappings' => $maps])->assertJsonPath('data.valid', true);
        return $uuid;
    }

    public function test_admin_sees_all_organizations(): void
    {
        $admin = $this->makeAdmin();

        Organization::create(['name_en' => 'Alpha', 'name_ar' => 'ألفا', 'user_id' => $admin->id]);
        Organization::create(['name_en' => 'Beta',  'name_ar' => 'بيتا', 'user_id' => $admin->id]);

        $resp = $this->actingAs($admin)->getJson('/dashboard/importer/organizations');
        $resp->assertStatus(200)->assertJsonPath('success', true);
        $orgs = $resp->json('data.organizations');
        $this->assertGreaterThanOrEqual(2, count($orgs));
    }

    public function test_preview_returns_mapped_columns_and_first_three_rows(): void
    {
        $user = $this->makeAdmin();
        $uuid = $this->completeUpToStep3($user);

        $resp = $this->actingAs($user)->getJson("/dashboard/importer/sessions/{$uuid}/preview?rows=3");
        $resp->assertStatus(200)
             ->assertJsonPath('success', true);

        $columns = $resp->json('data.columns');
        $this->assertNotEmpty($columns);
        $this->assertSame('program_name', $columns[0]['field_key']);
        $this->assertSame('Program Name', $columns[0]['label_en']);

        $rows = $resp->json('data.rows');
        $this->assertLessThanOrEqual(3, count($rows));
        $this->assertSame('Accelerator A', $rows[0]['program_name']);
    }

    public function test_preview_requires_validated_or_later(): void
    {
        $user = $this->makeAdmin();
        $create = $this->actingAs($user)->postJson('/dashboard/importer/sessions');
        $uuid   = $create->json('data.session_id');

        $resp = $this->actingAs($user)->getJson("/dashboard/importer/sessions/{$uuid}/preview");
        $resp->assertStatus(409);
    }

    public function test_preview_denies_other_users(): void
    {
        $owner = $this->makeAdmin();
        $intruder = $this->makeAdmin();
        $uuid = $this->completeUpToStep3($owner);

        $resp = $this->actingAs($intruder)->getJson("/dashboard/importer/sessions/{$uuid}/preview");
        $resp->assertStatus(404);
    }
}
