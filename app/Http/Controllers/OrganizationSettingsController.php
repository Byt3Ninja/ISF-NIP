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
