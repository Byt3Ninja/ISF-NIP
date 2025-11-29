<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\User\StoreUserRequest;
use App\Services\UserService;
use App\User;
use App\Country;
use App\Settings;
use App\UsersBio;
use App\Events\WebMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Broadcasting\BroadcastException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */

    protected $redirectTo = '/dashboard';

    /**
	 * UserController constructor.
	 * @param \App\Services\UserService $userService
	 */
	protected $userService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->middleware('guest');
        $this->userService = $userService;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

    return Validator::make($data, (new StoreUserRequest())->rules());
    }
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {


       $user= $this->userService->store($data);
    
         return $user['data'];

  
    }
}
