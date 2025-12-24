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

    protected function orgSettings()
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        $orgId = !empty($orgIds) ? $orgIds[0] : null;
        return OrganizationSettings::firstOrCreate(['organization_id' => $orgId]);
    }

    public function updateSmtp(Request $request)
    {
        $this->authorizeAccess();

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

        $settings = $this->orgSettings();
        $settings->fill($data)->save();

        return back()->with('message', 'Settings Updated Successfully');
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
        $settings->fill($payload)->save();

        return back()->with('message', 'Settings Updated Successfully');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!($user->hasRole('organizationManager') || $user->hasRole('Admin'))) {
            abort(403);
        }
    }
}
