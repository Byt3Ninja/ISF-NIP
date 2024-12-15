<?php

 namespace App\Http\Controllers;
 use App\Events\WebMessage;
 use Illuminate\Http\Request;
 use Validator,Redirect,Response,File;

 use App\User;
 use Illuminate\Support\Str;
 use Illuminate\Support\Facades\Hash;
 use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

 class SocialController extends Controller
 {

    public function redirect($provider){
      // dd(config('services.google.client_id'), config('services.google.client_secret'), config('services.google.redirect'));
      
        return Socialite::driver($provider)->redirect();
    }
    public function callback($provider){
        $getInfo = Socialite::driver($provider)->user();

        
        $user = $this->createUser($getInfo,$provider);
      if ($user == [1]) {
      return redirect()->to('/login')->withErrors(['social'=>'error']);
      }
      	auth()->login($user);
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
          $user->assignRole('Registered');
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
//                if ($finduser){
//                  return redirect()->to('/login')->withErrors(['social'=>'error']);
//                }

                // $finduser->update(['google_id'=> $guser->id,'email_verified_at' => now()]);
            }
            if($finduser){
              	auth()->login($finduser);
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
				$newUser->assignRole('Registered');
                event(new WebMessage('NewUser', $guser->name, 1));
              	auth()->login($newUser);
                return redirect('/dashboard');
            }

        } catch (Exception $e) {
            return redirect('auth/google');
        }
    }

    // ===========> LinkedIn <==========
    public function redirectToLinkedIn()
    {
      
    return Socialite::driver('linkedin')->stateless()->redirect();
    }

    public function handleLinkedInCallback()
    {
      
      try {
        // جلب بيانات المستخدم من LinkedIn
        $linkedInUser = Socialite::driver('linkedin')->user();
        
        // تقسيم الاسم الكامل إلى الاسم الأول واسم العائلة
        $name = explode(' ', $linkedInUser->getName(), 2); // أول جزئين من الاسم

        // التحقق مما إذا كان المستخدم موجودًا في قاعدة البيانات
        $existingUser = User::where('email', $linkedInUser->getEmail())->first();

        if ($existingUser) {
            // تسجيل دخول المستخدم إذا كان موجودًا
            auth()->login($existingUser);
        } else {
            // إنشاء مستخدم جديد
            $newUser = User::create([
                'first_name_en' => $name[0] ?? '', // الاسم الأول
                'last_name_en'  => $name[1] ?? '', // اسم العائلة
                'email'         => $linkedInUser->getEmail(),
                'password'      => Hash::make('no need for password token based'),
                'linkedin_id'   => $linkedInUser->getId(), // معرف LinkedIn
                'email_verified_at' => now(),
            ]);

            // تعيين دور للمستخدم (مثال: مسجل)
            $newUser->assignRole('Registered');

            // إطلاق حدث مخصص عند إنشاء مستخدم جديد
            event(new WebMessage('NewUser', $linkedInUser->getName(), 1));

            // تسجيل دخول المستخدم الجديد
            auth()->login($newUser);
        }

        // إعادة توجيه المستخدم إلى لوحة التحكم
        return redirect('/dashboard');
    } catch (\Exception $e) {
      dd($e);
        // في حالة حدوث خطأ أثناء عملية تسجيل الدخول
        return redirect('/login')->with(['error' => 'Failed to log in using LinkedIn.']);
    }
    }


 }
