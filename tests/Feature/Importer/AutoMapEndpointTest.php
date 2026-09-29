<?php

namespace Tests\Feature\Importer;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AutoMapEndpointTest extends TestCase
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

    private function uploadAndGetUuid(User $user, string $fixture = 'valid_minimal.csv'): string
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

    public function test_returns_mappings_plus_matched_count(): void
    {
        $user = $this->makeUser();
        $uuid = $this->uploadAndGetUuid($user);

        $resp = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/automap");

        $resp->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.mappings.program_name', 0)
             ->assertJsonPath('data.mappings.founder_email', 6);

        $this->assertGreaterThan(0, $resp->json('data.matched_count'));
        $this->assertSame(
            __('importer.ui.automap_messages.matched', [], 'en'),
            $resp->json('data.message_en')
        );
    }

    public function test_returns_unmatched_message_when_nothing_matches(): void
    {
        Storage::fake('importer');
        $user = $this->makeUser();

        $tmp = tempnam(sys_get_temp_dir(), 'nomatch_');
        rename($tmp, $tmp . '.csv');
        $tmp = $tmp . '.csv';
        file_put_contents($tmp, "Widget,Gadget,Gizmo\n1,2,3\n");

        $create = $this->actingAs($user)->postJson('/dashboard/importer/sessions');
        $uuid   = $create->json('data.session_id');
        $file   = new UploadedFile($tmp, 'nomatch.csv', 'text/csv', null, true);
        $this->actingAs($user)->post(
            "/dashboard/importer/sessions/{$uuid}/file",
            ['file' => $file],
            ['Accept' => 'application/json']
        )->assertStatus(200);

        $resp = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/automap");
        $resp->assertStatus(200)
             ->assertJsonPath('data.matched_count', 0)
             ->assertJsonPath('data.message_en', __('importer.ui.automap_messages.unmatched', [], 'en'));

        @unlink($tmp);
    }

    public function test_requires_uploaded_file(): void
    {
        $user = $this->makeUser();
        $create = $this->actingAs($user)->postJson('/dashboard/importer/sessions');
        $uuid   = $create->json('data.session_id');

        $resp = $this->actingAs($user)->postJson("/dashboard/importer/sessions/{$uuid}/automap");
        $resp->assertStatus(409);
    }

    public function test_denies_access_to_other_users_session(): void
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        $uuid = $this->uploadAndGetUuid($owner);

        $resp = $this->actingAs($intruder)->postJson("/dashboard/importer/sessions/{$uuid}/automap");
        $resp->assertStatus(404);
    }
}
