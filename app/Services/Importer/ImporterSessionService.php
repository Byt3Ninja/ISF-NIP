<?php

namespace App\Services\Importer;

use App\Services\Importer\Exceptions\InvalidStateTransitionException;
use App\Services\Importer\Exceptions\SessionNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImporterSessionService
{
    public const EXPIRY_HOURS = 24;
    public const AUDIT_RETENTION_DAYS = 30;

    private const ALLOWED_TRANSITIONS = [
        ImporterSession::STATUS_UPLOADED => [
            ImporterSession::STATUS_VALIDATED,
            ImporterSession::STATUS_DISCARDED,
        ],
        ImporterSession::STATUS_VALIDATED => [
            ImporterSession::STATUS_CONFLICTS_REVIEWED,
            ImporterSession::STATUS_IMPORTING,   // allowed when no conflicts detected
            ImporterSession::STATUS_DISCARDED,
        ],
        ImporterSession::STATUS_CONFLICTS_REVIEWED => [
            ImporterSession::STATUS_IMPORTING,
            ImporterSession::STATUS_DISCARDED,
        ],
        ImporterSession::STATUS_IMPORTING => [
            ImporterSession::STATUS_COMPLETED,
            ImporterSession::STATUS_FAILED,
        ],
        // terminal states have no outgoing transitions
        ImporterSession::STATUS_COMPLETED => [],
        ImporterSession::STATUS_FAILED    => [],
        ImporterSession::STATUS_DISCARDED => [],
    ];

    public function create(int $userId, string $originalFilename): ImporterSession
    {
        return ImporterSession::create([
            'uuid'              => (string) Str::uuid(),
            'user_id'           => $userId,
            'status'            => ImporterSession::STATUS_UPLOADED,
            'original_filename' => $originalFilename,
            'expires_at'        => Carbon::now()->addHours(self::EXPIRY_HOURS),
        ]);
    }

    public function findForUserByUuid(string $uuid, int $userId): ImporterSession
    {
        $session = ImporterSession::where('uuid', $uuid)
            ->where('user_id', $userId)
            ->first();

        if (!$session) {
            throw new SessionNotFoundException($uuid);
        }

        return $session;
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    public function transitionTo(ImporterSession $session, string $target): ImporterSession
    {
        if (!$this->canTransition($session->status, $target)) {
            throw new InvalidStateTransitionException($session->status, $target);
        }

        $session->status = $target;
        $session->save();

        return $session;
    }

    /**
     * Atomically move a ready (validated / conflicts-reviewed) session to IMPORTING.
     * False when another request already claimed it -- an in-memory status check
     * followed by save() let two concurrent executes both import the same file.
     */
    public function claimForImport(ImporterSession $session): bool
    {
        $claimed = ImporterSession::whereKey($session->getKey())
            ->whereIn('status', [ImporterSession::STATUS_VALIDATED, ImporterSession::STATUS_CONFLICTS_REVIEWED])
            ->update(['status' => ImporterSession::STATUS_IMPORTING]) > 0;

        if ($claimed) {
            $session->status = ImporterSession::STATUS_IMPORTING;
            $session->syncOriginalAttribute('status');
        }

        return $claimed;
    }

    public function touchExpiresAt(ImporterSession $session): ImporterSession
    {
        $session->expires_at = Carbon::now()->addHours(self::EXPIRY_HOURS);
        $session->save();
        return $session;
    }

    public function cleanupFile(ImporterSession $session): ImporterSession
    {
        if ($session->file_path && Storage::disk('importer')->exists($session->file_path)) {
            Storage::disk('importer')->delete($session->file_path);
            // best-effort folder removal; ignore if missing
            $folder = dirname($session->file_path);
            if ($folder !== '' && $folder !== '.' && Storage::disk('importer')->exists($folder)) {
                Storage::disk('importer')->deleteDirectory($folder);
            }
        }
        $session->file_path = null;
        $session->save();
        return $session;
    }

    public function discard(ImporterSession $session): ImporterSession
    {
        $this->cleanupFile($session);
        if ($session->status !== ImporterSession::STATUS_DISCARDED) {
            // Allow discard from anywhere, even terminal (idempotent — no-op if already terminal-non-discarded)
            if ($session->isTerminal()) {
                return $session;
            }
            $session->status = ImporterSession::STATUS_DISCARDED;
            $session->save();
        }
        return $session;
    }
}
