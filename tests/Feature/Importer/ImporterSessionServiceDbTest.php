<?php

namespace Tests\Feature\Importer;

use App\Services\Importer\ImporterSession;
use App\Services\Importer\ImporterSessionService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImporterSessionServiceDbTest extends TestCase
{
    use DatabaseTransactions;   // NEVER RefreshDatabase — project rule

    private function svc(): ImporterSessionService
    {
        return new ImporterSessionService();
    }

    private function fakeDisk(): void
    {
        Storage::fake('importer');
    }

    public function test_create_session_sets_uploaded_status_and_expiry(): void
    {
        $user = User::factory()->create();
        $session = $this->svc()->create($user->id, 'test.csv');

        $this->assertSame(ImporterSession::STATUS_UPLOADED, $session->status);
        $this->assertSame('test.csv', $session->original_filename);
        $this->assertNotNull($session->uuid);
        $this->assertTrue($session->expires_at->greaterThan(now()->addHours(23)));
    }

    public function test_cleanup_file_removes_disk_content_and_nulls_path(): void
    {
        $this->fakeDisk();
        $user = User::factory()->create();
        $session = $this->svc()->create($user->id, 'test.csv');
        Storage::disk('importer')->put($session->uuid . '/source.csv', 'abc');
        $session->file_path = $session->uuid . '/source.csv';
        $session->save();

        $this->svc()->cleanupFile($session);

        Storage::disk('importer')->assertMissing($session->uuid . '/source.csv');
        $this->assertNull($session->fresh()->file_path);
    }

    public function test_discard_transitions_to_discarded_and_removes_file(): void
    {
        $this->fakeDisk();
        $user = User::factory()->create();
        $session = $this->svc()->create($user->id, 'test.csv');
        Storage::disk('importer')->put($session->uuid . '/source.csv', 'abc');
        $session->file_path = $session->uuid . '/source.csv';
        $session->save();

        $this->svc()->discard($session);

        $fresh = $session->fresh();
        $this->assertSame(ImporterSession::STATUS_DISCARDED, $fresh->status);
        $this->assertNull($fresh->file_path);
    }

    public function test_find_for_user_throws_on_missing(): void
    {
        $user = User::factory()->create();
        $this->expectException(\App\Services\Importer\Exceptions\SessionNotFoundException::class);
        $this->svc()->findForUserByUuid('nonexistent-uuid', $user->id);
    }

    public function test_find_for_user_throws_on_other_users_session(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $session = $this->svc()->create($owner->id, 'x.csv');

        $this->expectException(\App\Services\Importer\Exceptions\SessionNotFoundException::class);
        $this->svc()->findForUserByUuid($session->uuid, $intruder->id);
    }
}
