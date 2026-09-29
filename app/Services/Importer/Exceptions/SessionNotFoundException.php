<?php

namespace App\Services\Importer\Exceptions;

class SessionNotFoundException extends ImporterException
{
    public function __construct(string $uuid)
    {
        parent::__construct(
            "Importer session not found: {$uuid}",
            'session_not_found'
        );
    }
}
