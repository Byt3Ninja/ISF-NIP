<?php

namespace App\Http\Controllers;

use App\OrganizationSettings;
use Illuminate\Http\Request;

class OrganizationSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', '2fa']);
    }

    protected function orgSettings(): ?OrganizationSettings
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        $orgId = $orgIds[0] ?? null;

        if (!$orgId) {
            return null;
        }

        return OrganizationSettings::firstOrCreate(['organization_id' => $orgId]);
    }

    public function updateSmtp(Request $request)
    {
        $this->authorizeAccess();

        $emailProvider = $request->input('email_provider', 'smtp');

        if ($emailProvider === 'resend') {
            $request->validate([
                'resend_api_key' => ['required', 'string', 'max:64'],
            ], [
                'resend_api_key.required' => __('settings.api_key_required'),
            ]);

            if (!\App\Services\ResendApiKeyValidator::validate($request->input('resend_api_key'))) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('settings.invalid_api_key')], 422);
                }
                return back()->with('message2', __('settings.invalid_api_key'));
            }

            $settings = $this->orgSettings();
            if (!$settings) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('settings.no_organization_linked')], 422);
                }
                return back()->with('message2', __('settings.no_organization_linked'));
            }
            $settings->fill([
                'email_provider' => 'resend',
                'resend_api_key' => $request->input('resend_api_key'),
            ])->save();

            if ($request->expectsJson()) {
                return response()->json(['message' => __('settings.email_config_saved')]);
            }
            return back()->with('message', 'Settings_Updated_Successfully');
        }

        // SMTP provider (existing logic)
        $data = $request->validate([
            'smtp_host' => ['nullable', 'string', 'max:191'],
            'smtp_port' => ['nullable', 'integer'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,null'],
            'smtp_username' => ['nullable', 'string', 'max:191'],
            'smtp_password' => ['nullable', 'string', 'max:191'],
            'smtp_from_address' => ['nullable', 'email'],
            'smtp_from_name' => ['nullable', 'string', 'max:191'],
        ]);

        // Normalize 'null' encryption string to null
        if (($data['smtp_encryption'] ?? null) === 'null') {
            $data['smtp_encryption'] = null;
        }

        $data['email_provider'] = 'smtp';

        $settings = $this->orgSettings();
        if (!$settings) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('settings.no_organization_linked')], 422);
            }
            return back()->with('message2', __('settings.no_organization_linked'));
        }
        $settings->fill($data)->save();

        return back()->with('message', 'Settings_Updated_Successfully');
    }

    public function updateNotifications(Request $request)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'accept_email' => ['nullable', 'boolean'],
            'accept_whatsapp' => ['nullable', 'boolean'],
            'accept_pusher' => ['nullable', 'boolean'],
        ]);

        // When checkboxes are unchecked, they are not sent; default to false
        $payload = [
            'accept_email' => (bool) ($data['accept_email'] ?? false),
            'accept_whatsapp' => (bool) ($data['accept_whatsapp'] ?? false),
            'accept_pusher' => (bool) ($data['accept_pusher'] ?? false),
        ];

        $settings = $this->orgSettings();
        if (!$settings) {
            return back()->with('error', __('settings.no_organization_linked'));
        }
        $settings->fill($payload)->save();

        return back()->with('message', 'Settings_Updated_Successfully');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['organizationManager', 'subadmin', 'Admin'])) {
            abort(403);
        }
    }
}
