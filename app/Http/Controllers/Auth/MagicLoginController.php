<?php

namespace App\Http\Controllers\Auth;

use App\Activity;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class MagicLoginController extends Controller
{
    public function login(Request $request, $userId, $token)
    {
        $user = User::find($userId);

        if (! $user || ! $user->magic_login_token || ! hash_equals($user->magic_login_token, (string) $token)) {
            abort(403);
        }

        $user->magic_login_token = null;
        $user->must_change_password = true;
        $user->email_verified_at = now();
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        $this->logActivity();

        $request->session()->flash('must_change_password', true);

        return redirect()
            ->route('user.profile_Edit')
            ->with('warning', __('admin.Please_change_your_password_after_first_login'));
    }

    private function logActivity(): void
    {
        try {
            $agent = new Agent();
            $browser = $agent->browser();
            $os = $agent->platform();
            $device = $agent->device();
            $ip = request()->ip();
            $browserWithOs = $browser ? "$browser on $os" : 'Unknown Browser';

            Activity::create([
                'user_id' => Auth::id(),
                'browser' => $browserWithOs,
                'device' => $device,
                'location' => $this->locationFromIp($ip),
                'activity_time' => now(),
                'ip_address' => $ip,
                'platform' => $os,
            ]);
        } catch (\Throwable $e) {
            Log::warning('MagicLogin activity log failed: ' . $e->getMessage());
        }
    }

    private function locationFromIp(string $ip): string
    {
        try {
            $response = Http::timeout(3)->get("https://ipwho.is/{$ip}");
            return $response->json()['country'] ?? 'Unknown Location';
        } catch (\Throwable $e) {
            return 'Unknown Location';
        }
    }
}
