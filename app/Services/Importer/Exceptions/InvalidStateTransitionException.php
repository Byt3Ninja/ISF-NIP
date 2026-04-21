<?php

namespace App\Services\Importer\Exceptions;

class InvalidStateTransitionException extends ImporterException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct(
            "Invalid importer session transition: {$from} -> {$to}",
            'invalid_state_transition'
        );
    }
}
