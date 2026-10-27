<?php

 namespace App\Http\Controllers;
 use App\Events\WebMessage;
 use Illuminate\Http\Request;
 use Validator,Redirect,Response,File;

 use App\User;
 use App\Settings;
 use Illuminate\Support\Str;
 use Illuminate\Support\Facades\Hash;
 use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

 class SocialController extends Controller
 {

    /** Socialite drivers this generic flow is configured for (Settings holds Google credentials only). */
    private const PROVIDERS = ['google'];

    public function redirect($provider){
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
    //   dd(config('services.LINKEDIN_CLIENT_ID'), config('services.LINKEDIN_CLIENT_SECRET'), config('services.LINKEDIN_REDIRECT_URI'));
        $settings = Settings::where('id', 1)->first();
        $config = [
            'services.google.client_id' => $settings->GOOGLE_CLIENT_ID ?? '',
            'services.google.client_secret' => $settings->GOOGLE_CLIENT_SECRET ?? '',
            'services.google.redirect' => $settings->GOOGLE_REDIRECT ?? '',
        ];
        config( $config );
        return Socialite::driver($provider)->redirect();
    }
    public function callback($provider){
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        $settings = Settings::where('id', 1)->first();
        $config = [
            'services.google.client_id' => $settings->GOOGLE_CLIENT_ID ?? '',
            'services.google.client_secret' => $settings->GOOGLE_CLIENT_SECRET ?? '',
            'services.google.redirect' => $settings->GOOGLE_REDIRECT ?? '',
        ];
        config( $config );
        $getInfo = Socialite::driver($provider)->user();

        
        $user = $this->createUser($getInfo,$provider);
      if ($user == [1]) {
      return redirect()->to('/login')->withErrors(['social'=>'error']);
      }
      	auth()->login($user);
        request()->session()->regenerate();
        return redirect()->to('/dashboard');
    }
 
    function createUser($getInfo,$provider){
        $user = User::where('provider_id', $getInfo->id)->first();
      	if (!$user) {
        $user = User::where('email', $getInfo->email)->first();
          if ($user){return $user = [1];}
        			}
        if (!$user) {
          	$name = explode(" ",$getInfo->name);
            $user = User::create([
                'first_name_en' => $name[0],
              	'last_name_en' => $name[1],
                'email'    => $getInfo->email,
              	'password' => Hash::make('no need for password token based'),
                'provider' => $provider,
                'provider_id' => $getInfo->id,
              	'email_verified_at' => now()
            ]);
          $user->allotRole('Registered');
          event(new WebMessage('NewUser', $getInfo->name, 1));
        }
        return $user;
    }

    public function redirectToGoogle(){
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback(){
      
        try {
            $guser = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $guser->id)->first();
            
			if (!$finduser) {
                $finduser = User::where('email', $guser->email)->first();
                // Only sign in to an existing account by email when Google vouches for that address.
                if ($finduser && !$this->providerVerifiedEmail($guser->user ?? [])) {
                    return redirect()->to('/login')->withErrors(['social' => 'error']);
                }
            }
            if($finduser){
              	auth()->login($finduser);
                request()->session()->regenerate();
                return redirect('/dashboard');
            }else{
                $name = explode(" ",$guser->name);
                $newUser = User::create([
                  	'first_name_en'     => $name[0],
              		'last_name_en' => $name[1],
                    'email' => $guser->email,
                    'password' => Hash::make('no need for password token based'),
                    'google_id'=> $guser->id,
					'email_verified_at' => now()
                ]);
				$newUser->allotRole('Registered');
                event(new WebMessage('NewUser', $guser->name, 1));
              	auth()->login($newUser);
                request()->session()->regenerate();
                return redirect('/dashboard');
            }

        } catch (\Exception $e) {
            return redirect('auth/google');
        }
    }

    // ===========> LinkedIn <==========
    public function redirectToLinkedIn($data = null)
    {
        // $data is kept for old links (/auth/linkedin/2521); it no longer picks a role.
        // Every new LinkedIn account is a Founder, like the Google flow.
        $state = Str::random(40);
        session(['linkedin_oauth_state' => $state]);
    
        // dd(config('services.linkedin.client_id'), config('services.linkedin.client_secret'), config('services.linkedin.redirect'));
        $LINKEDIN_CLIENT_ID = config('services.linkedin.client_id');
        $LINKEDIN_CLIENT_SECRET = config('services.linkedin.client_secret');
        $LINKEDIN_REDIRECT_URI = config('services.linkedin.redirect');
        
        $authorizationUrl = 'https://www.linkedin.com/oauth/v2/authorization';
        $clientId = $LINKEDIN_CLIENT_ID;
        $redirectUri = $LINKEDIN_REDIRECT_URI;
        $scope = 'openid profile email'; // الأذونات المطلوبة
    
        $url = $authorizationUrl . '?response_type=code'
            . '&client_id=' . $clientId
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&scope=' . urlencode($scope)
            . '&state=' . urlencode($state);
            
        return redirect($url);
    }
    
    public function handleLinkedInCallback(Request $request)
    {
        
        if (!$request->has('code')) {
            return redirect()->route('login')->with('error', 'Authorization code is missing');
        }
        $expectedState = (string) session()->pull('linkedin_oauth_state', '');
        if ($expectedState === '' || !hash_equals($expectedState, (string) $request->get('state'))) {
            return redirect()->route('login')->with('error', 'Unable to authenticate');
        }
        $LINKEDIN_CLIENT_ID = config('services.linkedin.client_id');
        $LINKEDIN_CLIENT_SECRET = config('services.linkedin.client_secret');
        $LINKEDIN_REDIRECT_URI = config('services.linkedin.redirect');


        $clientId = $LINKEDIN_CLIENT_ID;
        $clientSecret = $LINKEDIN_CLIENT_SECRET;
        $redirectUri = $LINKEDIN_REDIRECT_URI;
        $authorizationCode = $request->get('code');
    
        try {
            $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);
            
            if ($response->successful()) {
                $accessToken = $response->json()['access_token'];
                $userInfo = $this->getUserInfo($accessToken);
                $userInfo = json_decode($userInfo, true);
                  
                if ($userInfo) {
                    

                  $user = User::where('linkedin_id', $userInfo['sub'])->first();

                  if ($user == null) {
                      // حاول العثور على المستخدم باستخدام البريد الإلكتروني
                      $user = User::where('email', $userInfo['email'] ?? null)->first();
                      if ($user != null) {
                          // Existing account: link it only when LinkedIn vouches for the address,
                          // and never touch its profile or roles.
                          if (!$this->providerVerifiedEmail($userInfo)) {
                              return redirect()->route('login')->with('error', 'Unable to authenticate');
                          }
                          $user->linkedin_id = $userInfo['sub'] ?? '';
                          $user->save();
                          auth()->login($user);
                          request()->session()->regenerate();
                          return redirect('/dashboard');
                      }
                      // إذا لم يتم العثور على المستخدم، قم بإنشاء مستخدم جديد
                      $user = new User;
                      // تعيين بيانات المستخدم
                      $user->first_name_en = $userInfo['given_name'] ?? '';
                      $user->last_name_en = $userInfo['family_name'] ?? '';
                      $user->first_name_ar = $userInfo['given_name'] ?? '';
                      $user->last_name_ar = $userInfo['family_name'] ?? '';
                      $user->email = $userInfo['email'] ?? '';
                      $user->linkedin_id = $userInfo['sub'] ?? '';
                      $user->img_url = $userInfo['picture'] ?? null;
                      // ضبط كلمة المرور إذا كانت غير موجودة
                      if (!$user->password) {
                          $user->password = Hash::make('no need for password token based');
                      }
                      // ضبط حالة التحقق من البريد الإلكتروني
                      if (!empty($userInfo['email_verified']) && $userInfo['email_verified'] == true) {
                          $user->email_verified_at = now();
                      }
                      $user->save();
                      $user->allotRole('Registered');
                  } else {
                      // تحديث linkedin_id إذا تم العثور على المستخدم
                      $user->linkedin_id = $userInfo['sub'] ?? '';
                      $user->save();
                  }
                    auth()->login($user);
                    request()->session()->regenerate();
                    return redirect('/dashboard');
                }
            }
    
            return redirect()->route('login')->with('error', $response->json()['error_description'] ?? 'Unable to authenticate');
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('login')->with('error', 'An unexpected error occurred');
        }
    }
    
    /** OIDC-style claim from Google/LinkedIn: did the provider verify this email address? */
    private function providerVerifiedEmail(array $claims): bool
    {
        $verified = $claims['email_verified'] ?? $claims['verified_email'] ?? false;

        return $verified === true || $verified === 'true' || $verified === 1 || $verified === '1';
    }

    private function getUserInfo($accessToken)
    {
        // طلب للحصول على بيانات المستخدم
        $response = Http::withToken($accessToken)
            ->get('https://api.linkedin.com/v2/userinfo');
        if ($response->successful()) {
          return $response->body(); 
        }
        return null; // في حالة فشل طلب البيانات
    }


 }
