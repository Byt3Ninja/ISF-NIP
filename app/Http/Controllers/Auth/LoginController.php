<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Activity; 
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
	  
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // protected function attemptLogin(Request $request)
    // {
    //     return $this->guard()->attempt(
    //         $this->credentials($request),
    //         $request->filled('remember') // تحقق من وجود خيار remember
    //     );
    // }

    protected function authenticated(Request $request, $user)
    {
        // Initialize Agent for device/browser detection
        $agent = new Agent();
        $browser = $agent->browser();
        $os = $agent->platform();
        $device = $agent->device();
        $clientIp = $this->getClientIp();
        $location = $this->getLocationFromIP($clientIp);
        $browserWithOs = $browser ? "$browser on $os" : 'Unknown Browser';

       Activity::create([
            'user_id' => auth()->id(),
            'browser' => $browserWithOs,
            'device' => $device,
            'location' => $location,
            'activity_time' => now(),
            'ip_address' => $clientIp,
            'platform' => $os,
        ]);

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Get the client's public IP address.
     */
    private function getClientIp()
    {
        $ip = request()->ip();

        try {
               $response = Http::get('https://api.ipify.org?format=json');
               $data = $response->json();
               $ip = $data['ip'] ?? $ip;
            } catch (\Exception $e) {
                Log::error('Failed to get client IP: ' . $e->getMessage());
        }
     
        return $ip;
    }

    /**
     * Get country based on IP address.
     */
    private function getLocationFromIP($ip)
    {
        try {
            $response = Http::get("http://ip-api.com/json/{$ip}");
            $data = $response->json();

            return $data['country'] ?? 'Unknown Location';
        } catch (\Exception $e) {
            Log::error('Failed to get location: ' . $e->getMessage());
            return 'Unknown Location';
        }
    }

}
