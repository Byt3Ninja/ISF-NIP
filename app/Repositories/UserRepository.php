<?php

namespace App\Repositories;

use App\Role;
use App\User;
use App\Cycle;
use App\Company;
use App\Country;
use App\UsersBio;
use App\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\serviceProviderRegistration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Mail\serviceProviderRegistrationMail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\FileManagerService;

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

        if (!empty($filters['r'])) {
            $query->whereHas('roles', fn($q) => $q->where('name', $filters['r']));
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
    public function paginate(Builder $query,  $perPage = 10, $page=null, $direction = 'desc'): LengthAwarePaginator
    {
        $page = $page ?? Paginator::resolveCurrentPage();

        return $query->with(['roles', 'company'])->orderBy('created_at', $direction)->paginate($perPage, ['*'], 'page', $page);
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
                $user = User::create(attributes: [
                    'first_name_en' => $data['first_name'],
                    'last_name_en' => $data['last_name'],
                    //this is a bug #1232
                    'first_name_ar' => $data['first_name'],
                    'last_name_ar' => $data['last_name'],
                    //end bug
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'country_code' => $data['country_code'],
                    'gender' => $data['gender'] ?? 'male',
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => setting('VFEmail') == 1 ? null : now(),
                    'must_change_password' => array_key_exists('must_change_password', $data) ? $data['must_change_password'] : false,
                ]);

                if (isset($data['img_url']) && $data['img_url']) {
                    $img_url = $data['img_url'];
                    $img_url = app(FileManagerService::class)->upload($img_url, 'users', 'public_uploads', 'image');
                    $user->img_url = $img_url;
                }

                if ($data['role'] == "mentor" && !auth()?->user()?->hasRole('admin'))
                {
                    UsersBio::create([
                        'user_id' => $user->id,
                        'bio_en' => $data['bio_en'],
                        'linkedin' => $data['Linkedin'],
                    ]);

                    if (!empty($data['expertise']))
                    {
                        $expertise = [];
                        foreach ($data['expertise'] as $expertiseId) {
                            $expertise[] = [
                                'expertise_id' => $expertiseId,
                                'mentor_id' => $user->id
                            ];
                        }
                        DB::table('mentor_expertise')->insert($expertise);

                    }
                    $user->title_en = $data['title_en'];
                }
                // default_for first: role names can be renamed, default_for is the stable key.
                $role = Role::where('default_for', $data['role'])->first();
                if(!$role)
                {
                    $role = Role::where('name', $data['role'])->first();
                }
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ]);

                $user->save();

                return $user->fresh(['roles']);
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

    public function registerServiceProvider(array $data)
    {
        try {
            $email = $data['email'];
            /* $user = User::where('email', $email)->first();
            if($user){
                return [
                    'success' => false,
                    'message' => 'Email already exists',
                    'data'    => null,
                    'errors'  => ['Email already exists']
                    ];
            } */
            if( array_key_exists('contact_name', $data) )
            {
                $parse_name = explode(' ', $data['contact_name']);
                $first_name = $parse_name[0];
                $last_name = isset($parse_name[1]) ? $parse_name[1] : '';
                $data['first_name'] = $first_name;
                $data['last_name'] = $last_name;
                $contact_name = $data['contact_name'];
                $user = $data['user'];
                $full_name = trim( $data['first_name'] . ' ' . $data['last_name'] );
                $registration = new serviceProviderRegistration;
                $registration->company_name = $full_name;
                $registration->contact_name = $full_name;
                $registration->email = $data['email'];
                $registration->country_code = $data['country_code'];
                $registration->phone = '+' . $data['country_code'] . $data['phone'];
                $registration->status = null;
                $registration->user_id = null;
                $registration->ip_address = request()->ip();
                $registration->save();

                if( !in_array(env('APP_ENV', 'local'), ['local']) )
                {
                    if( config('mail.driver') && config('mail.host') && config('mail.port') && config('mail.username') && config('mail.password') )
                    {
                        Mail::to($registration->email)->queue(new serviceProviderRegistrationMail($contact_name, $data['email'], $user->generateMagicLoginUrl(), app()->getLocale()));
                    }
                }
            }
            return [
                'success' => true,
                'message' => 'Service Provider registered successfully',
                'data'    => $registration,
                'errors'  => null
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to register Service Provider',
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
                $user->first_name_en = $data['first_name'] ?? $user->first_name_en;
                $user->last_name_en = $data['last_name'] ?? $user->last_name_en;
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
                $user->email_verified_at = $data['email_verified_at'] ?? null;

                if (isset($data['img_url']) && $data['img_url'])
                {
                    $img_url = $data['img_url'];
                    $img_url = app(FileManagerService::class)->upload($img_url, 'users', 'public_uploads', 'image');
                    $user->img_url = $img_url;
                }

                if( !empty($data['password']) ) { $user->password = Hash::make($data['password']); }

                $user->save();

                if (isset($data['role']))
                {
                    $currentUser = auth()->user();
                    if (!$currentUser || !$currentUser->hasRole('Admin')) {
                        throw new \Illuminate\Auth\Access\AuthorizationException('Unauthorized role change.');
                    }

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
            $user->status = $user->status === 'inactive' ? 'active' : 'inactive';
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
        try {
            $roles = Role::all();
            $rolesData = $roles->map(function ($role) {
                $users = User::role($role->name)->get();

                return [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'default_for' => $role->default_for,
                    'totalUsers'  => $users->count(),
                    'avatars'     => $users->map(function ($user) {
                        return $user->img_url ? Storage::disk('public_uploads')->url($user->img_url) : null;
                    })->filter()->values()->toArray(),
                ];
            });

            return [
                'success' => true,
                'message' => 'Roles List successfully',
                'data'    => $rolesData,
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


    /**
     * Summary of updatePassword
     * @param \App\User $user
     * @param array $data
     * @return array{message: array|string|null, status: int, success: bool|array{message: string, status: int, success: bool}}
     */
    public function updatePassword(User $user, array $data): array
    {
        try {
            $password = Hash::make($data['password']);
            $user->where('id', $user->id)->update([
                'password' => $password,
                'must_change_password' => false,
                'magic_login_token' => null,
            ]);

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

    /**
      * Summary of getCompaniesForRegisteredUser
      * @param \App\User $user
      * @param array $filters
      * @return array{companies: Builder[]|Collection<TKey, TValue>|\Illuminate\Database\Eloquent\Collection, errorMessage: string|null}
      */
    public function getCompaniesForRegisteredUser(User $user, array $filters): array
    {
        $l = app()->getLocale();
        $name = ($l == 'ar') ? 'name_ar' : 'name_en';
        $search = $filters['search'] ?? null;
        $sector = $filters['sector'] ?? null;
        $stage = $filters['stage'] ?? null;
        $companies = collect();
        $errorMessage = '';

        $company = $user->company;
        $orgIds = $user->organizations_ids;
        if ($company && $company->cycle != null)
        {
            $companies = Company::when(!empty($orgIds), function ($q) use ($orgIds) {
                    return $q->whereIn('organization_id', $orgIds);
                })
                ->where('cycle', $company->cycle)
                ->when($search, function ($query) use ($search, $name) {
                    $query->where($name, 'like', '%' . $search . '%');
                })
                ->when($sector, function ($query) use ($sector) {
                    $query->where('sector', 'like', '%' . $sector . '%');
                })
                ->when($stage, function ($query) use ($stage) {
                    $query->where('stage', 'like', '%' . $stage . '%');
                })
                ->where('id', $company->id)
                ->get();
        } else {
            $errorMessage = $company && $company->cycle === null ? 'انت غير مشترك في البرامج الحالية' : null;
        }

        return ['companies' => $companies, 'errorMessage' => $errorMessage];
    }

    /**
     * Summary of getCompaniesForOrgManager
     * Supports both single organization (organizationManager) and multiple organizations (Sub-Admin)
     * @param \App\User $user
     * @param array $filters
     * @return array{companies: mixed, errorMessage: string}
     */
    public function getCompaniesForOrgManager(User $user, array $filters): array
    {
        $l = app()->getLocale();
        $name = ($l == 'ar') ? 'name_ar' : 'name_en';
        $search = $filters['search'] ?? null;
        $sector = $filters['sector'] ?? null;
        $stage = $filters['stage'] ?? null;
        $companies = collect();

        // Get organization IDs - supports both single and multiple organizations
        $orgIds = $user->organizations_ids;

        if (empty($orgIds)) {
            return ['companies' => $companies, 'errorMessage' => ''];
        }

        $cycle_ids = Cycle::whereIn('organization_id', $orgIds)->pluck('id');
        $companies = Company::whereIn('cycle', $cycle_ids)
            ->when($search, function ($query) use ($search, $name) {
                $query->where($name, 'like', '%' . $search . '%');
            })
            ->when($sector, function ($query) use ($sector) {
                $query->where('sector', 'like', '%' . $sector . '%');
            })
            ->when($stage, function ($query) use ($stage) {
                $query->where('stage', 'like', '%' . $stage . '%');
            })
            ->get();

        return ['companies' => $companies, 'errorMessage' => ''];
    }

    /**
     * Summary of getCompaniesForMentor
     * @param array $filters
     * @return array{companies: Builder[]|\Illuminate\Database\Eloquent\Collection, errorMessage: string}
     */
    public function getCompaniesForMentor(array $filters): array
    {
        $l = app()->getLocale();
        $name = ($l == 'ar') ? 'name_ar' : 'name_en';
        $search = $filters['search'] ?? null;
        $sector = $filters['sector'] ?? null;
        $stage = $filters['stage'] ?? null;

        $companies = Company::query()
            ->when($search, function ($query) use ($search, $name) {
                $query->where($name, 'like', '%' . $search . '%');
            })
            ->when($sector, function ($query) use ($sector) {
                $query->where('sector', 'like', '%' . $sector . '%');
            })
            ->when($stage, function ($query) use ($stage) {
                $query->where('stage', 'like', '%' . $stage . '%');
            })
            ->get();

        return ['companies' => $companies, 'errorMessage' => ''];
    }

    /**
     * Summary of getCompaniesForAdmin
     * @param array $filters
     * @return array{companies: Builder[]|\Illuminate\Database\Eloquent\Collection, errorMessage: string}
     */
    public function getCompaniesForAdmin(array $filters): array
    {
        $l = app()->getLocale();
        $name = ($l == 'ar') ? 'name_ar' : 'name_en';
        $search = $filters['search'] ?? null;
        $sector = $filters['sector'] ?? null;
        $stage = $filters['stage'] ?? null;
        $organization = $filters['orgnization'] ?? null;

        $query = Company::query();

        if ($organization) {
            $cycle_ids = Cycle::where('organization_id', $organization)->pluck('id');
            $query->whereIn('cycle', $cycle_ids);
        }

        $companies = $query->when($search, function ($query) use ($search, $name) {
                $query->where($name, 'like', '%' . $search . '%');
            })
            ->when($sector, function ($query) use ($sector) {
                $query->where('sector', 'like', '%' . $sector . '%');
            })
            ->when($stage, function ($query) use ($stage) {
                $query->where('stage', 'like', '%' . $stage . '%');
            })
            ->get();

        return ['companies' => $companies, 'errorMessage' => ''];
    }

    /**
     * Summary of importUsers
     * @param array $userData
     * @return array{message: string, success: bool}
     */
    public function importUsers(array $userData): array
    {
        try {
            DB::beginTransaction();
            foreach ($userData as $row)
            {
                $user = User::create([
                    'email'    => $row[1],
                    'first_name_en'    => $row[2],
                    'last_name_en'    => $row[3],
                    'first_name_ar'    => $row[2],
                    'last_name_ar'    => $row[3],
                    'phone'         => $row[4] . "" . $row[5],
                    'HaveCompany'    => (int)$row[8],
                    'country_id'    => (int)$row[7],
                    'organization_id'    => '18',
                    'password' => Hash::make('123456789'),
                    'email_verified_at' => now(),
                    'must_change_password' => true,
                ]);
                $user->allotRole('Registered');

                if( isset( $row[8] ) && (int) $row[8] == 1 )
                {
                    $company = Company::create([
                        'country_id'    => (int)$row[7],
                        'name_en'    => $row[9],
                        'name_ar' => $row[9],
                        'address_en' => $row[9],
                        'address_ar' => $row[9],
                        'country_code' => $row[4],
                        'phone' => $row[5],
                        'whatsapp' => (int)$row[5],
                        'email' => $row[1],
                        'cycle' => '58',
                        'step' => '220',
                        'approved' => '1',
                        'approved_by' => '1',
                        'institutions' => '0',
                        'user_id' => $user->id,
                    ]);

                    $user->company_id = $company->id;
                    $user->save();
                }
            }
            DB::commit();
            return ['success' => true, 'message' => 'تم رفع الشيت بنجاح!'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User import failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'فشل استيراد المستخدمين.'];
        }
    }

    public function createRoleWithPermissions(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
            ]);

            $flattened = collect($data['permissions'])->flatten()->toArray();
            $permissions = Permission::whereIn('name', $flattened)->pluck('id')->toArray();

            $role->permissions()->sync($permissions);
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return $role->load('permissions');
        });
    }

    public function updateRoleWithPermissions(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $role = Role::findOrFail($id);

            if (isset($data['name'])) {
                $role->name = $data['name'];
            }

            $role->save();
            if (isset($data['permissions'])) {
                $flattened = collect($data['permissions'])->flatten()->toArray();
                $permissions = Permission::whereIn('name', $flattened)->pluck('id')->toArray();
                $role->permissions()->syncWithoutDetaching($permissions);
                app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            }

            return $role->load('permissions');
        });
    }
}
