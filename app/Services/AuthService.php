<?php

namespace App\Services;

use App\User;
use App\Settings;
use App\UsersBio;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Notifications\WelcomeUserNotification;
use App\Notifications\WelcomeMentorNotification;
use App\Support\NotificationClaim;


class AuthService
{
    use ApiResponder;

    /** API register role (RegisterUserRequest) => roles.default_for. */
    private const REGISTER_ROLES = ['Mentor' => 'mentor', 'Registered' => 'registered'];

    public function attemptLogin(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new \Exception(json_encode(['type' => 'credential', 'message' => __('api.incorrect_credentials')]));
        }

        if ($user->status !== 'active') {
            throw new \Exception(json_encode(['type' => 'inactive', 'message' => __('api.inactive_user')]));
        }   


        return $user;
    }

    public function generateToken(User $user): string
    {
        return $user->createToken('auth-login')->plainTextToken;
    }

    public function register(array $data): array
    {
        $settings = Settings::find(1);

        $userData = [
            'first_name_en' => $data['firstName'],
            'last_name_en'  => $data['lastName'],
            'first_name_ar' => $data['firstName'],
            'last_name_ar'  => $data['lastName'],
            'gender'        => $data['gender'],
            'phone'         => $data['phone'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'title_en'      => $data['title'] ?? null,
            'title_ar'      => $data['title'] ?? null,
        ];

        if ($settings && $settings->VFEmail != 1) {
            $userData['email_verified_at'] = now();
        }

        DB::beginTransaction();
        try {
            // forceCreate: email_verified_at is deliberately not fillable, and create() dropped it.
            $user = User::forceCreate($userData);

            // Role names can be renamed; default_for is the stable key.
            $role = Role::where('default_for', self::REGISTER_ROLES[$data['role']])->firstOrFail();

            DB::table('model_has_roles')->insert([
                'role_id'    => $role->id,
                'model_type' => User::class,
                'model_id'   => $user->id,
             ]);

           if ($role->default_for === 'mentor') {
                UsersBio::create([
                    'user_id'  => $user->id,
                    'bio_en'   => $data['userbio'] ?? null,
                    'linkedin' => $data['linkedin'] ?? null,
                ]);

                if (!empty($data['expertise'])) {
                    $expertiseInsert = collect($data['expertise'])->map(fn($expertiseId) => [
                        'expertise_id' => $expertiseId,
                        'mentor_id'    => $user->id,
                    ])->toArray();

                    DB::table('mentor_expertise')->insert($expertiseInsert);
                }

                $sendMentorWelcome = true;
            }
            DB::commit();

            // Only once the account is committed, and once per account; a failed send
            // frees the slot again (NotificationClaim).
            try {
                if (! empty($sendMentorWelcome)) {
                    NotificationClaim::on('users', ['id' => $user->id], 'mentor_welcome_sent_at')
                        ->run(fn (NotificationClaim $claim) => $user->notify((new WelcomeMentorNotification($user))->withClaim($claim)));
                } else {
                    NotificationClaim::on('users', ['id' => $user->id], 'user_welcome_sent_at')
                        ->run(fn (NotificationClaim $claim) => $user->notify((new WelcomeUserNotification($user))->withClaim($claim)));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $token = $user->createToken('auth-register')->plainTextToken;

            return [
                'success' => true,
                'user' => $user,
                'token' => $token
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }

    public function logout(Request $request, ?User $user)
{
      $bearerToken = $request->bearerToken();

    if (! $bearerToken || ! $user || ! $user->currentAccessToken()) {
        return $this->unauthorized(__('api.unauthenticated'));
    }

    $user->currentAccessToken()->delete();

    return $this->success(null, [], __('api.logged_out'), 200);
}
}
