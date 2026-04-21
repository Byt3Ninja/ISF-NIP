<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\ImporterSession;
use App\Services\Importer\ImporterSessionService;
use App\Services\Importer\Exceptions\InvalidStateTransitionException;
use PHPUnit\Framework\TestCase;

class ImporterSessionServiceTest extends TestCase
{
    public function test_valid_forward_transitions_allowed(): void
    {
        $svc = new ImporterSessionService();
        $chain = [
            ImporterSession::STATUS_UPLOADED           => ImporterSession::STATUS_VALIDATED,
            ImporterSession::STATUS_VALIDATED          => ImporterSession::STATUS_CONFLICTS_REVIEWED,
            ImporterSession::STATUS_CONFLICTS_REVIEWED => ImporterSession::STATUS_IMPORTING,
            ImporterSession::STATUS_IMPORTING          => ImporterSession::STATUS_COMPLETED,
        ];
        foreach ($chain as $from => $to) {
            $this->assertTrue($svc->canTransition($from, $to), "should allow {$from} -> {$to}");
        }
    }

    public function test_validated_may_skip_to_importing_when_no_conflicts(): void
    {
        $svc = new ImporterSessionService();
        $this->assertTrue($svc->canTransition(
            ImporterSession::STATUS_VALIDATED,
            ImporterSession::STATUS_IMPORTING
        ));
    }

    public function test_any_state_can_go_to_discarded(): void
    {
        $svc = new ImporterSessionService();
        foreach ([
            ImporterSession::STATUS_UPLOADED,
            ImporterSession::STATUS_VALIDATED,
            ImporterSession::STATUS_CONFLICTS_REVIEWED,
        ] as $status) {
            $this->assertTrue($svc->canTransition($status, ImporterSession::STATUS_DISCARDED));
        }
    }

    public function test_importing_can_go_to_failed(): void
    {
        $svc = new ImporterSessionService();
        $this->assertTrue($svc->canTransition(
            ImporterSession::STATUS_IMPORTING,
            ImporterSession::STATUS_FAILED
        ));
    }

    public function test_terminal_states_cannot_transition_out(): void
    {
        $svc = new ImporterSessionService();
        foreach (ImporterSession::TERMINAL_STATUSES as $terminal) {
            $this->assertFalse(
                $svc->canTransition($terminal, ImporterSession::STATUS_VALIDATED),
                "terminal '{$terminal}' should not transition"
            );
        }
    }

    public function test_backwards_transition_forbidden(): void
    {
        $svc = new ImporterSessionService();
        $this->assertFalse($svc->canTransition(
            ImporterSession::STATUS_VALIDATED,
            ImporterSession::STATUS_UPLOADED
        ));
    }

    public function test_transitionTo_throws_on_invalid(): void
    {
        $this->expectException(InvalidStateTransitionException::class);
        $svc = new ImporterSessionService();
        $session = new ImporterSession(['status' => ImporterSession::STATUS_COMPLETED]);
        $svc->transitionTo($session, ImporterSession::STATUS_VALIDATED);
    }
}
