<?php

namespace Tests\Support\Fakes;

use App\Services\WhatsAppService;

/** Records WhatsApp messages instead of sending them. */
class FakeWhatsAppService extends WhatsAppService
{
    /** @var array<int, array{phone: mixed, message: mixed}> */
    public array $sent = [];

    public function __construct()
    {
        // No config / HTTP client needed.
    }

    public function sendMessage($phoneNumber, $message, $userOrganization = null)
    {
        $this->sent[] = ['phone' => $phoneNumber, 'message' => $message];

        return ['success' => true];
    }

    public function sendOTP($phoneNumber, $otp)
    {
        $this->sent[] = ['phone' => $phoneNumber, 'message' => "OTP $otp"];

        return ['success' => true];
    }
}
