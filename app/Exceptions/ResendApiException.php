<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

/**
 * A failed call to the Resend REST API, keeping Resend's own error name
 * (e.g. "restricted_api_key", "validation_error") and HTTP status so callers
 * can translate the cases the UI cares about.
 *
 * @see https://resend.com/docs/api-reference/errors
 */
class ResendApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly ?string $type = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public static function fromResponse(Response $response): self
    {
        return new self(
            (string) ($response->json('message') ?? ''),
            $response->status(),
            $response->json('name') ?? $response->json('type'),
        );
    }

    public static function network(Throwable $previous): self
    {
        return new self('', 0, 'network_error', $previous);
    }

    /**
     * A key that can only send email ("sending access"). It is a real key,
     * but it cannot list or add domains, which the connect flow needs.
     */
    public function isSendingOnlyKey(): bool
    {
        return $this->type === 'restricted_api_key'
            && str_contains(strtolower($this->getMessage()), 'only send');
    }

    /**
     * The key itself was refused: missing, unknown, inactive or suspended.
     * A malformed key comes back as 400 validation_error "API key is invalid".
     */
    public function isKeyRejected(): bool
    {
        return $this->status === 401
            || in_array($this->type, ['missing_api_key', 'invalid_api_key', 'restricted_api_key', 'suspended_api_key'], true)
            || (in_array($this->status, [400, 403], true) && str_contains(strtolower($this->getMessage()), 'api key'));
    }

    /**
     * Sending from a domain that has not passed DNS verification in Resend.
     */
    public function isDomainNotVerified(): bool
    {
        return $this->status === 403
            && str_contains(strtolower($this->getMessage()), 'not verified');
    }
}
