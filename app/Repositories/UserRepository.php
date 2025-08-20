<?php

namespace App\Repositories;

use App\Role;
use App\User;
use App\Country;
use App\UsersBio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class UserRepository
{
    /**
     *
     * Get the base User query builder.
     */
    public function baseQuery(): Builder
    {
        return User::query();
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Fillter And Search User 
     * @param array $filters
     * @return Builder   
     * This method allows filtering users based on various criteria
     */
    public function filter(array $filters): Builder
    {
        $query = $this->baseQuery();

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['haveCompany'])) {
            $query->where('HaveCompany', $filters['haveCompany']);
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', fn($q) => $q->where('name', $filters['role']));
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
        return $query;


    }

    /**
     * This method paginates the results of a query, allowing for easy navigation through large datasets.
     * @param Builder $query
     * @param int $perPage
     * @param string $direction
     * @return LengthAwarePaginator
     */
    public function paginate(Builder $query, int $perPage = 10, $direction = 'desc'): LengthAwarePaginator
    {
        return $query->orderBy('created_at', $direction)->paginate($perPage);

    }

    /**
     * Create a new user.
     * @param array $data
     * @return array|User
     * @throws \Exception
     * This method creates a new user in the database, handling role assignment and transaction management.
     */
    public function create(array $data)
    {
        try {
            $user = DB::transaction(function () use ($data) {


                $user = User::create([
                    'first_name_en' => $data['firstName'],
                    'first_name_ar' => $data['firstName'],
                    'last_name_en' => $data['lastName'],
                    'last_name_ar' => $data['lastName'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'country_code' => $data['country_code'],
                    'gender' => $data['gender'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => setting('VFEmail') == 1 ? null : now(),
                ]);

                    if (isset($data['img_url']) && $data['img_url']) {
                        $img_url = $data['img_url'];
                        $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
                        $user->img_url = $img_url;
                    }

                if ($data['role'] == "4" && !auth()->user()->hasRole('admin')) {
                    dd('sdasdas');
                    UsersBio::create([
                        'user_id' => $user->id,
                        'bio_en' => $data['bio_en'],
                        'linkedin' => $data['Linkedin'],
                    ]);

                    if (!empty($data['expertise'])) {

                        $expertises = [];
                        foreach ($data['expertise'] as $expertiseId) {
                            $expertises[] = [
                                'expertise_id' => $expertiseId,
                                'mentor_id' => $user->id
                            ];
                        }
                        DB::table('mentor_expertise')->insert($expertises);

                    }




                    $user->title_en = $data['title_en'];

                }
                    $role = Role::find($data['role']);
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ]);


                    $user->save();

                    return $user->fresh();
            });


            return [
            'success' => true,
            'message' => 'User created successfully',
            'data'    => $user,
            'errors'  => null
            ];

        } catch (\Throwable $e) {
            return [
            'success' => false,
            'message' => 'Failed to create user',
            'data'    => null,
            'errors'  => [$e->getMessage()]
            ];
        }
    }

    /**
     * Update a user.
     * @param User $user
     * @param array $data
     * @return User|array
     * @throws \Exception
     * This method updates an existing user in the database, handling role assignment and transaction management.
     */
    public function update(User $user, array $data)
    {
        try {
            $updatedUser = DB::transaction(function () use ($user, $data) {

                $user->phone = $data['phone'] ?? $user->phone;
                $user->first_name_en = $data['firstName'] ?? $user->first_name_en;
                $user->last_name_en = $data['lastName'] ?? $user->last_name_en;
                $user->first_name_ar = $data['firstName'] ?? $user->first_name_ar;
                $user->last_name_ar = $data['lastName'] ?? $user->last_name_ar;
                $user->email = $data['email'] ?? $user->email;
                $user->gender = $data['gender'] ?? $user->gender;
                $user->status = $data['status'] ?? $user->status;
                $user->country_id = $data['country_id'] ?? $user->country_id;
                $user->HaveCompany = $data['HaveCompany'] ?? $user->HaveCompany;
                $user->houre_rate = $data['houre_rate'] ?? $user->houre_rate;
                $user->state_id = $data['state_id'] ?? $user->state_id;
                $user->city_id = $data['city_id'] ?? $user->city_id;
                $user->title_en = $data['title_en'] ?? $user->title_en;
                $user->title_ar = $data['title_ar'] ?? $user->title_ar;
                $user->country_code = $data['country_code'] ?? $user->country_code;



                if (!empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                }

                $user->save();

                if (isset($data['role'])) {
                    DB::table('model_has_roles')
                        ->where('model_type', User::class)
                        ->where('model_id', $user->id)
                        ->delete();

                    DB::table('model_has_roles')->insert([
                        'role_id' => $data['role'],
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ]);
                }

                return $user->fresh(); // return the updated user object
            });

            return $updatedUser;


        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }


    /**
     * Deactivate a user.
     * @param User $user
     * @return bool
     * This method deactivates a user by setting their status to 'inactive'.
     */
    public function deactivate(User $user): bool|array
    {
        try {

            $user = User::find(id: $user->id);
            if ($user->status === 'inactive') {
                $user->status = 'active';
            } else {
                $user->status = 'inactive';
            }
            $user->save();

            return true;

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }

    }
    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        $user->roles()->detach();
        $user->delete();
        $user->company()->delete();
        return true;
    }

    /**
     * Get all users.
     */
    public function all(): Collection
    {
        return User::all();
    }

    /**
     * get all Roles
     */
    public function roles()     
    {
        try{

        
    $roles= Role::all();
            return [
                'success' => true,
                'message' => 'Roles List successfully',
                'data'    => $roles,
                'errors'  => null
                ];
        } catch (\Throwable $e) {
            return [
            'success' => false,
            'message' => 'Failed to create user',
            'data'    => null,
            'errors'  => [$e->getMessage()]
            ];
        }

    }
    /**
     * get List of Country
     */
    public function countries(): collection
    {
        $countries = country::all();
        $json = File::get(base_path('database/dbFiles/countries.json'));
        $_countries = json_decode($json, true);
        $_countries = collect($_countries)->map(function ($country) {
            $country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
            return $country;
        });
        $countries = $countries->map(function ($country) use ($_countries) {
            $getCountry = $_countries->where('iso3', $country->iso3)->first();
            $flag = null;
            $emoji = null;
            if (!empty($getCountry)) {
                $flag = $getCountry['flag'];
                $emoji = $getCountry['emoji'];
            }
            $country->flag = $flag;
            $country->emoji = $emoji;
            return $country;
        });
        return $countries;
    }



    public function updatePassword(User $user, array $data): array
    {
        try {
            $password = Hash::make($data['password']);
            $user->where('id', $user->id)->update(['password' => $password]);

            return [
                'success' => true,
                'message' => __('api.password_updated_successfully'),
                'status' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }
}
