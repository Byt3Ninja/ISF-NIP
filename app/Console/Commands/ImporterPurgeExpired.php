<?php

namespace App\Console\Commands;

use App\Services\Importer\ImporterSession;
use App\Services\Importer\ImporterSessionService;
use Illuminate\Console\Command;

class ImporterPurgeExpired extends Command
{
    protected $signature   = 'importer:purge-expired';
    protected $description = 'Discard importer sessions whose expires_at is in the past and delete their files.';

    public function handle(ImporterSessionService $svc): int
    {
        $count = 0;
        ImporterSession::query()
            ->where('expires_at', '<', now())
            ->whereNotIn('status', ImporterSession::TERMINAL_STATUSES)
            ->orderBy('id')
            ->chunk(200, function ($sessions) use ($svc, &$count) {
                foreach ($sessions as $session) {
                    $svc->discard($session);
                    $count++;
                }
            });

        $this->info("Purged {$count} expired importer session(s).");
        return self::SUCCESS;
    }
}
