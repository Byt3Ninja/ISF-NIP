<?php

namespace App\Services;

use Log;
use App\User;
use App\Cycle;
use App\InviteMentor;
use App\Mail\WelcomeUserMail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrganizationCreatedMail;
use Google\Service\ServiceControl\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\WelcomeUserNotification;
use App\Notifications\WelcomeMentorNotification;
use App\Notifications\WelcomeJudgerNotification;


class UserService
{ 
    /**
     * Summary of userRepository
     * @var \App\Repositories\UserRepository
     */
    protected $userRepository;
    
    function __construct()
    {
        // You can inject dependencies here if needed
        $this->userRepository = new \App\Repositories\UserRepository();

    }
    /**
     * Summary of index
     * @param array $filters
     * @return array{data: \Illuminate\Http\Resources\Json\AnonymousResourceCollection, meta: array}
     */
    public function index(array $filters,$paginate): array
    {

        $query = $this->userRepository->filter($filters);
        if($paginate){        
        $users = $this->userRepository->paginate($query, $filters['per_page'] ?? 10, $filters['page'] ?? 1,'desc');
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
        }else {
            $users = $query->with(['roles', 'company'])->get();
        }
        return [
            'data' => UserResource::collection($users),

        ];
    }
    /**
     * Summary of show
     * @param int $id
     * @return User|User[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function show(int $id): ?User
    {
        return User::find($id);
    }
    /**
     * Summary of store
     * @param array $data
     * @throws \Exception
     * @return array{message: string, status: int, success: bool|array{success: bool, user: mixed}}
     */
	public function store(array $data): array
	{
		try{
			$result = $this->userRepository->create( $data );
			if( $result['success'] == true )
			{
				if( $data['role'] == 'serviceProvider' && array_key_exists('contact_name', $data) )
				{
					$data['user'] = $result['data'];
					$result_service_provider = $this->userRepository->registerServiceProvider( $data );
				}
				$update_invite_mentor = InviteMentor::where('email', $data['email'])->orderBy('created_at', 'desc')->first();
				if($update_invite_mentor)
				{
					$update_invite_mentor->update([
						'mentor_id' => $result['data']->id,
						'status' => 'Accepted',
					]);
					if($update_invite_mentor->organization_ids != null){
						$mentorId = $result['data']->id;
						$cycles = Cycle::whereIn(
							'organization_id',
							$update_invite_mentor->organization_ids
						)->get();
						foreach ($cycles as $cycle) {
							$cycle->mentors()->syncWithoutDetaching([$mentorId]);
						}
					}
				}
				$user = $result['data'];
				Storage::disk('UsersLFM')->makeDirectory("$user->id");
				Storage::disk('UsersLFM')->makeDirectory("$user->id/BusinessPlans");
				Storage::disk('UsersLFM')->makeDirectory("$user->id/FinancialPlans");
				Storage::disk('UsersLFM')->makeDirectory("$user->id/PitchDeck");
				Storage::disk('UsersLFM')->makeDirectory("$user->id/Shared");
				Storage::disk('UsersLFM')->makeDirectory("$user->id/Others");

				$isJudge = $user->hasRole('Judger')
					|| $user->hasRole('judge')
					|| in_array(strtolower($data['role'] ?? ''), ['judger', 'judge']);

				if($isJudge) {
					if ($user->claimEmailSlot('judge_welcome_sent_at')) {
						$user->notify(new WelcomeJudgerNotification($user));
					}
				}
				elseif($user->hasRole('mentor') || strtolower($data['role'] ?? '') === 'mentor') {
					if ($user->claimEmailSlot('mentor_welcome_sent_at')) {
						$user->notify(new WelcomeMentorNotification($user, $data['password']));
					}
				}
				else {
					if ($user->claimEmailSlot('user_welcome_sent_at')) {
						$user->notify(new WelcomeUserNotification($user, $data['password']));
					}
				}
			}
			return $result;
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
     * Summary of update
     * @param \App\User $user
     * @param array $data
     * @return array{message: string, status: int, success: bool}
     */
    public function update(User $user, array $data) 
    {
        try {
            $updated = $this->userRepository->update($user, $data);

            return [
                'success' => true,
                'user' => $updated
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
     * Summary of deactivateUser
     * @param \App\User $user
     * @param mixed $id
     * @return array{message: array|string|null, status: int, success: bool|array{success: bool}}
     */
    public function deactivateUser(User $user ,$authId): array
    {

        if ($user->id == $authId) {
            return [
                'success' => false,
                'message' => __('api.cannot_deactivate_self'),
                'status' => 403
            ];
        }

            $result= $this->userRepository->deactivate($user);
        

        return [
            'success' => $result,
        ];
    }

    /**
     * Summary of roleList
     * @return array{data: null, errors: string[], message: string, success: bool|array{data: \Illuminate\Database\Eloquent\Collection<int, \App\Role>, errors: null, message: string, success: bool}}
     */
    public function roleList()
    {
      $result=$this->userRepository->roles();

      return $result;
    }
    /**
     * Summary of countries
     * @return \Illuminate\Support\Collection
     */
    public function countries()
    {
        $result=$this->userRepository->countries();
        return $result;
    }
    /**
     * Summary of updatePassword
     * @param \App\User $user
     * @param array $data
     * @return array{message: array|string|null, status: int, success: array{message: string, status: int, success: bool|bool}}
     */
    public function updatePassword(User $user, array $data): array
    {
      $result=$this->userRepository->updatePassword($user, $data);
      return $result;
    }

    /**
     * Summary of destroy
     * @param \App\User $user
     * @return bool
     */
    public function destroy(User $user): bool
    {
        $result = $this->userRepository->delete($user);
        return $result;
    }
    /**
     * Summary of getCompanies
     * @param array $requestData
     * @param \App\User $user
     * @return array{companies: Builder[]|\Illuminate\Database\Eloquent\Collection, errorMessage: string|array{companies: Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection<\App\Repositories\TKey, \App\Repositories\TValue>, errorMessage: string|null}|array{companies: mixed, errorMessage: string}}
     */
    public function getCompanies(array $requestData, User $user): array
    {
        if ($user->hasRole('Registered')) {
            return $this->userRepository->getCompaniesForRegisteredUser($user, $requestData);
        }

        // Handle both organizationManager and subadmin roles
        if ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            return $this->userRepository->getCompaniesForOrgManager($user, $requestData);
        }

        if ($user->hasRole('Mentor')) {
            return $this->userRepository->getCompaniesForMentor($requestData);
        }

        // Assuming admin or other roles
        return $this->userRepository->getCompaniesForAdmin($requestData);
    }
    /**
     * Summary of importUsers
     * @param \Illuminate\Http\UploadedFile $file
     * @return array{message: string, success: bool}
     */
    public function importUsers(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $data = $spreadsheet->getActiveSheet()->toArray();

            // Assuming the first row is the header, skip it
            array_shift($data);

            return $this->userRepository->importUsers($data);
        } catch (\Exception $e) {
            Log::error('User import file processing failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'فشل معالجة ملف استيراد المستخدمين.'];
        }
    }

     public function createRole(array $data)
    {
        try {
            $role = $this->userRepository->createRoleWithPermissions($data);

            return [
                'success' => true,
                'message' => __('api.role_created'),
                'data' => [
                    'roleId' => $role->id,
                    'roleName' => $role->name,
                    'permissions' => collect($data['permissions'])->filter(fn($group) => count($group) > 0),
                    'createdAt' => $role->created_at->toIso8601String(),
                    'updatedAt' => $role->updated_at->toIso8601String(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ];
        }
    }

    public function updateRolePermissions(int $id, array $data)
{
    try {
        $role = $this->userRepository->updateRoleWithPermissions($id, $data);
        $permissionsByGroup = $role->permissions
            ->groupBy('group')
            ->map(fn($items) => $items->pluck('name')->values());
        return [
            'success' => true,
            'message' => __('api.role_updated'),
            'data' => [
                'roleId' => $role->id,
                'roleName' => $role->name,
                'permissions' => $permissionsByGroup,
                'updatedAt' => $role->updated_at->toIso8601String(),
            ],
        ];
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return [
            'success' => false,
            'message' => __('api.role_not_found'),
            'status' => 404,
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
            'status' => 500,
        ];
    }
}


}