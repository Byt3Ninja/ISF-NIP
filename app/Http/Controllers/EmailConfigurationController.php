<?php

namespace App\Http\Controllers;

use App\Exceptions\ResendApiException;
use App\Organization;
use App\Services\EmailSettingsStore;
use App\Services\ResendApi;
use App\Services\TenantMailer;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Email configuration for the platform (System Settings) and for a single
 * organization (Organization Settings). Each route passes its scope through
 * the route defaults, so both screens share these actions and one form.
 *
 * GV-1513 (platform) / GV-1514 (organization).
 */
class EmailConfigurationController extends Controller
{
    private const DOMAIN_REGEX = '/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))*\.[a-z]{2,63}$/i';

    private const LOCAL_PART_REGEX = '/^[A-Za-z0-9._%+-]+$/';

    public function __construct(private ResendApi $resend)
    {
    }

    public function saveSmtp(Request $request, string $scope): JsonResponse
    {
        $store = $this->store($scope);
        $store->saveSmtp($this->validatedSmtp($request));

        return response()->json([
            'message' => __('settings.email_settings_saved'),
            'active'  => $store->activeConfig(),
        ]);
    }

    /**
     * Send a test email with the SMTP values on screen, before they are saved.
     */
    public function testSmtp(Request $request, string $scope): JsonResponse
    {
        $this->store($scope);
        $smtp = $this->validatedSmtp($request);
        $recipient = $request->user()->email;

        try {
            TenantMailer::apply([
                'transport'    => EmailSettingsStore::transportFor($smtp['preset']),
                'host'         => $smtp['host'],
                'port'         => (int) $smtp['port'],
                'encryption'   => $smtp['encryption'],
                'username'     => $smtp['username'],
                'password'     => $smtp['password'],
                'from_address' => $smtp['from_address'],
                'from_name'    => $smtp['from_name'] ?? null,
                'source'       => 'test',
            ]);

            Mail::raw(__('settings.test_email_body'), function (Message $message) use ($recipient) {
                $message->to($recipient)->subject(__('settings.test_email_subject'));
            });
        } catch (Throwable $e) {
            return response()->json([
                'message' => __('settings.test_email_failed'),
                'detail'  => Str::limit($e->getMessage(), 300),
            ], 422);
        } finally {
            TenantMailer::restore();
        }

        return response()->json(['message' => __('settings.test_email_sent', ['email' => $recipient])]);
    }

    /**
     * Step 1: check the API key and return the account's domains. Without a
     * key in the request, the saved key is used (changing the domain later).
     */
    public function resendConnect(Request $request, string $scope): JsonResponse
    {
        $store = $this->store($scope);
        $request->validate(['api_key' => ['nullable', 'string', 'max:64']], $this->messages());

        try {
            $domains = $this->resend->listDomains($this->apiKey($request, $store));
        } catch (ResendApiException $e) {
            return $this->resendError($e);
        }

        return response()->json([
            'message' => __('settings.api_key_connected'),
            'domains' => $domains,
        ]);
    }

    /**
     * Step 2 (optional): add a new domain to the connected Resend account.
     */
    public function resendAddDomain(Request $request, string $scope): JsonResponse
    {
        $store = $this->store($scope);
        $this->normalizeDomain($request);
        $request->validate([
            'api_key' => ['nullable', 'string', 'max:64'],
            'domain'  => ['required', 'string', 'max:255', 'regex:' . self::DOMAIN_REGEX],
        ], $this->messages());

        try {
            $domain = $this->resend->createDomain($this->apiKey($request, $store), $request->input('domain'));
        } catch (ResendApiException $e) {
            return $this->resendError($e, 'domain');
        }

        return response()->json([
            'message' => __('settings.domain_added'),
            'domain'  => $domain,
        ]);
    }

    /**
     * Step 3: send a test email to the signed-in user. Only a successful send
     * saves the configuration and makes it live.
     */
    public function resendTest(Request $request, string $scope): JsonResponse
    {
        $store = $this->store($scope);
        $this->normalizeDomain($request);
        $data = $request->validate([
            'api_key'    => ['nullable', 'string', 'max:64'],
            'domain'     => ['required', 'string', 'max:255', 'regex:' . self::DOMAIN_REGEX],
            'from_local' => ['required', 'string', 'max:64', 'regex:' . self::LOCAL_PART_REGEX],
            'from_name'  => ['nullable', 'string', 'max:191'],
        ], $this->messages());

        $apiKey = $this->apiKey($request, $store);
        $from = $data['from_local'] . '@' . $data['domain'];
        $fromName = $data['from_name'] ?? null;
        $recipient = $request->user()->email;

        try {
            $this->resend->sendEmail($apiKey, [
                'from'    => $fromName ? sprintf('"%s" <%s>', str_replace('"', '', $fromName), $from) : $from,
                'to'      => [$recipient],
                'subject' => __('settings.test_email_subject'),
                'text'    => __('settings.test_email_body'),
            ]);
        } catch (ResendApiException $e) {
            return $this->resendError($e);
        }

        $store->saveResend($apiKey, $data['domain'], $from, $fromName);

        return response()->json([
            'message'   => __('settings.email_config_connected'),
            'active'    => 'resend',
            'recipient' => $recipient,
        ]);
    }

    private function store(string $scope): EmailSettingsStore
    {
        if ($scope === 'platform') {
            return EmailSettingsStore::platform();
        }

        $user = auth()->user();
        abort_unless($user->hasAnyRole(['organizationManager', 'subadmin', 'Admin']), 403);

        // Same organization SettingsController@showSettings renders the page for.
        $organization = $user->organization ?? Organization::whereIn('id', $user->organizations_ids)->first();

        if (!$organization) {
            throw new HttpResponseException(
                response()->json(['message' => __('settings.no_organization_linked')], 422)
            );
        }

        return EmailSettingsStore::forOrganization($organization);
    }

    /**
     * Validate the SMTP form. A preset's fixed connection values always win
     * over anything posted.
     */
    private function validatedSmtp(Request $request): array
    {
        $preset = $request->validate([
            'smtp_preset' => ['required', Rule::in(array_keys(EmailSettingsStore::PRESETS))],
        ])['smtp_preset'];

        $fromName = ['nullable', 'string', 'max:191'];

        $data = match ($preset) {
            'gmail' => $request->validate([
                'username'  => ['required', 'email', 'max:191'],
                'password'  => ['required', 'string', 'max:191'],
                'from_name' => $fromName,
            ]),
            'resend' => $request->validate([
                'password'     => ['required', 'string', 'max:64'],
                'from_address' => ['required', 'email', 'max:191'],
                'from_name'    => $fromName,
            ], $this->messages()),
            'custom' => $request->validate([
                'host'         => ['required', 'string', 'max:191'],
                'port'         => ['required', 'integer', 'between:1,65535'],
                'encryption'   => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
                'username'     => ['required', 'string', 'max:191'],
                'password'     => ['required', 'string', 'max:191'],
                'from_address' => ['required', 'email', 'max:191'],
                'from_name'    => $fromName,
            ]),
            // Internal relay: no login and no encryption (fixed by the preset).
            'relay' => $request->validate([
                'host'         => ['required', 'string', 'max:191'],
                'port'         => ['required', 'integer', 'between:1,65535'],
                'from_address' => ['required', 'email', 'max:191'],
                'from_name'    => $fromName,
            ]),
        };

        if ($preset === 'gmail') {
            // Google shows app passwords in groups of four; Gmail sends as the account itself.
            $data['password'] = str_replace(' ', '', $data['password']);
            $data['from_address'] = $data['username'];
        }

        if (($data['encryption'] ?? null) === 'none') {
            $data['encryption'] = null;
        }

        return ['preset' => $preset] + EmailSettingsStore::PRESETS[$preset] + $data;
    }

    private function apiKey(Request $request, EmailSettingsStore $store): string
    {
        $apiKey = trim((string) $request->input('api_key')) ?: $store->resendApiKey();

        if (!$apiKey) {
            throw ValidationException::withMessages(['api_key' => __('settings.api_key_required')]);
        }

        return $apiKey;
    }

    private function normalizeDomain(Request $request): void
    {
        $request->merge(['domain' => strtolower(trim((string) $request->input('domain')))]);
    }

    /**
     * Turn a Resend API failure into the message the form shows. Problems with
     * a specific field come back under `errors`, the unverified domain under
     * `code`, anything else as a plain `message`.
     */
    private function resendError(ResendApiException $e, ?string $field = null): JsonResponse
    {
        if ($e->isSendingOnlyKey()) {
            return $this->fieldError('api_key', __('settings.resend_key_sending_only'));
        }

        if ($e->isKeyRejected()) {
            return $this->fieldError('api_key', __('settings.invalid_api_key'));
        }

        if ($e->isDomainNotVerified()) {
            return response()->json([
                'code'    => 'domain_not_verified',
                'message' => __('settings.resend_domain_not_verified'),
            ], 422);
        }

        Log::warning('Resend API call failed', [
            'status'  => $e->status,
            'type'    => $e->type,
            'message' => $e->getMessage(),
        ]);

        $message = $e->getMessage() ?: __('settings.email_config_connect_failed');

        // Resend's own validation messages (e.g. "domain has been registered already") belong to the field.
        if ($field && $e->getMessage() && in_array($e->status, [400, 403, 409, 422], true)) {
            return $this->fieldError($field, $message);
        }

        return response()->json(['message' => $message], $e->status === 0 ? 503 : 422);
    }

    private function fieldError(string $field, string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => [$field => [$message]]], 422);
    }

    private function messages(): array
    {
        return [
            'api_key.max'        => __('settings.api_key_too_long'),
            'password.max'       => __('settings.api_key_too_long'),
            'domain.required'    => __('settings.domain_required'),
            'domain.max'         => __('settings.domain_invalid'),
            'domain.regex'       => __('settings.domain_invalid'),
            'from_local.required' => __('settings.field_required'),
            'from_local.regex'   => __('settings.sender_local_invalid'),
        ];
    }
}
