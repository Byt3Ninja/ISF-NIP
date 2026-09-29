<?php

namespace Tests\Feature\Importer;

use App\Services\Importer\ImporterSession;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImporterPurgeExpiredCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_discards_expired_non_terminal_sessions(): void
    {
        Storage::fake('importer');
        $user = User::factory()->create();

        $expired = ImporterSession::create([
            'uuid'              => 'expired-uuid',
            'user_id'           => $user->id,
            'status'            => ImporterSession::STATUS_UPLOADED,
            'original_filename' => 'x.csv',
            'file_path'         => 'expired-uuid/source.csv',
            'expires_at'        => now()->subHour(),
        ]);
        Storage::disk('importer')->put('expired-uuid/source.csv', 'abc');

        $fresh = ImporterSession::create([
            'uuid'              => 'fresh-uuid',
            'user_id'           => $user->id,
            'status'            => ImporterSession::STATUS_UPLOADED,
            'original_filename' => 'y.csv',
            'file_path'         => 'fresh-uuid/source.csv',
            'expires_at'        => now()->addHour(),
        ]);
        Storage::disk('importer')->put('fresh-uuid/source.csv', 'def');

        $this->artisan('importer:purge-expired')->assertExitCode(0);

        $this->assertSame(ImporterSession::STATUS_DISCARDED, $expired->fresh()->status);
        $this->assertNull($expired->fresh()->file_path);
        Storage::disk('importer')->assertMissing('expired-uuid/source.csv');

        $this->assertSame(ImporterSession::STATUS_UPLOADED, $fresh->fresh()->status);
        Storage::disk('importer')->assertExists('fresh-uuid/source.csv');
    }

    public function test_command_skips_already_terminal_sessions(): void
    {
        Storage::fake('importer');
        $user = User::factory()->create();

        $done = ImporterSession::create([
            'uuid'              => 'completed-uuid',
            'user_id'           => $user->id,
            'status'            => ImporterSession::STATUS_COMPLETED,
            'original_filename' => 'z.csv',
            'file_path'         => null,
            'expires_at'        => now()->subDay(),
        ]);

        $this->artisan('importer:purge-expired')->assertExitCode(0);

        // Completed session stays completed (audit retention is a separate concern)
        $this->assertSame(ImporterSession::STATUS_COMPLETED, $done->fresh()->status);
    }
}
