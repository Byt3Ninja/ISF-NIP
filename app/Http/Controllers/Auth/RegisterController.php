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
use Illuminate\Validation\Rule;
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
     * Roles a visitor may pick on the public registration form => the role they receive.
     * Service providers register through /provider-registration, not here.
     */
    private const SELF_REGISTER_ROLES = ['Founder' => 'registered', 'Mentor' => 'mentor'];

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
        // StoreUserRequest is shared with admin user-creation, which may assign any role;
        // self-registration may only pick one of SELF_REGISTER_ROLES.
        $rules = (new StoreUserRequest())->rules();
        $rules['role'] = ['required', Rule::in(array_keys(self::SELF_REGISTER_ROLES))];

        return Validator::make($data, $rules);
    }
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        abort_unless(isset(self::SELF_REGISTER_ROLES[$data['role'] ?? '']), 422);
        $data['role'] = self::SELF_REGISTER_ROLES[$data['role']];

        $hasMentorPayload = !empty($data['title_en'])
            || !empty($data['bio_en'])
            || !empty($data['Linkedin'])
            || !empty($data['expertise']);
        if ($data['role'] === 'registered' && $hasMentorPayload) {
            $data['role'] = 'mentor';
        }

        $data['must_change_password'] = false;
        $user= $this->userService->store($data);
    
        return $user['data'];
    }
}
