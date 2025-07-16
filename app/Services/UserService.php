<?php

namespace App\Services;

use App\User;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    public function index(array $filters, User $authUser): array
    {
        $query = User::query();

        if ($authUser->hasRole('OrganizationManager')) {
            $query->where('organization_id', $authUser->organization_id);
        }

        if (!empty($filters['gender']) && in_array(strtolower($filters['gender']), ['male', 'female'])) {
            $query->where('gender', strtolower($filters['gender']));
        }

       if (array_key_exists('haveCompany', $filters)) {
    $value = $filters['haveCompany'];

    if (in_array($value, ['1', 1], true)) {
        $query->where('HaveCompany', 1);
    } elseif (in_array($value, ['0', 0], true)) {
        $query->where('HaveCompany', 0);
    }
}
      
        if (!empty($filters['role'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('first_name_en', 'like', "%$search%")
                  ->orWhere('last_name_en', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return [
            'data' => UserResource::collection($users),
            'meta' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total(),
                'path' => $users->path(),
                'links' => [
                    'first' => $users->url(1),
                    'last' => $users->url($users->lastPage()),
                    'prev' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ]
            ]
        ];
    }

    public function show(int $id): ?User
    {
        return User::find($id);
    }

    public function store(array $data): array
{
    try {
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name_en' => $data['firstName'],
                'last_name_en' => $data['lastName'],
                'first_name_ar' => $data['firstName'],
                'last_name_ar' => $data['lastName'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'password' => Hash::make($data['password']),
            ]);

            $role = Role::find($data['role']);
            if (!$role) {
                throw new \Exception(__('api.invalid_role'));
            }

            DB::table('model_has_roles')->insert([
                'role_id'    => $role->id,
                'model_type' => User::class,
                'model_id'   => $user->id,
            ]);
            Mail::to($user->email)->queue(new WelcomeUserMail($user, $data['password']));

            return $user->fresh();
        });

        return [
            'success' => true,
            'user' => $user,
        ];

    } catch (\Throwable $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'status' => 500
        ];
    }
}
 

  public function update(User $user, array $data): array
{
    try {
        $updated = DB::transaction(function () use ($user, $data) {
            if (isset($data['firstName'])) {
                $user->first_name_en = $data['firstName'];
                $user->first_name_ar = $data['firstName'];
            }

            if (isset($data['lastName'])) {
                $user->last_name_en = $data['lastName'];
                $user->last_name_ar = $data['lastName'];
            }

            if (isset($data['email'])) {
                $user->email = $data['email'];
            }

            if (isset($data['phone'])) {
                $user->phone = $data['phone'];
            }

            if (isset($data['gender'])) {
                $user->gender = $data['gender'];
            }

            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            if (isset($data['status'])) {
                $user->status = $data['status'];
            }

            $user->save();

            if (isset($data['role'])) {
                DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->where('model_id', $user->id)
                    ->delete();

                DB::table('model_has_roles')->insert([
                    'role_id'    => $data['role'],
                    'model_type' => User::class,
                    'model_id'   => $user->id,
                ]);
            }

            return $user->fresh();
        });

        return [
            'success' => true,
            'user' => $updated,
        ];
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'status' => 500,
        ];
    }
}

  public function deactivateUser(User $authUser, $id): array
{
    if ($authUser->id == $id) {
        return [
            'success' => false,
            'message' => __('api.cannot_deactivate_self'),
            'status' => 403
        ];
    }

    $user = User::find($id);

    if (!$user) {
        return [
            'success' => false,
            'message' => __('api.user_not_found'),
            'status' => 404
        ];
    }

    $user->status = 'inactive';
    $user->save();

    return [
        'success' => true
    ];
}
}
