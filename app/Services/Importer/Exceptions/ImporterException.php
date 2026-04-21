<?php

namespace App\Services\Importer\Exceptions;

use Exception;

class ImporterException extends Exception
{
    protected string $errorCode;

    public function __construct(string $message = '', string $errorCode = 'importer_error', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
