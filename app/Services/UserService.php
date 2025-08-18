<?php

namespace App\Services;

use App\Mail\OrganizationCreatedMail;
use App\Notifications\WelcomeJudgerNotification;
use App\User;
use App\Mail\WelcomeUserMail;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use App\Http\Resources\UserResource;
use Google\Service\ServiceControl\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Builder;

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
    public function index(array $filters): array
    {
        $query = $this->userRepository->filter($filters);
        $users = $this->userRepository->paginate($query, 10, 'desc');

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

 
        $result = $this->userRepository->create($data);
         
        if($result['success'] ==true){
        $user=$result['data'];
        Storage::disk('UsersLFM')->makeDirectory("$user->id");
		Storage::disk('UsersLFM')->makeDirectory("$user->id/BusinessPlans");
		Storage::disk('UsersLFM')->makeDirectory("$user->id/FinancialPlans");
		Storage::disk('UsersLFM')->makeDirectory("$user->id/PitchDeck");
		Storage::disk('UsersLFM')->makeDirectory("$user->id/Shared");
		Storage::disk('UsersLFM')->makeDirectory("$user->id/Others");


        if($user->hasRole('Judger')){
               $user->notify(new WelcomeJudgerNotification($user));
        }elseif( $user->hasRole('organizationmanager')){
                $org_owner= $user->organization();
               
                Mail::to($user->email)->queue(new OrganizationCreatedMail($org_owner,$user, $data['password']));

        }else{

             Mail::to(users: $user->email)->queue(new WelcomeUserMail($user, $data['password']));
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
     * get role list 
     */
    public function roleList()
    {
      $result=$this->userRepository->roles();

      return $result;
    }

    /**
     * get Country List
     */

    public function countries()
    {
        $result=$this->userRepository->countries();
        return $result;
    }

    public function updatePassword(User $user, array $data): array
    {
      $result=$this->userRepository->updatePassword($user, $data);
      return $result;
    }


    public function destroy(User $user): bool
    {
        $result = $this->userRepository->delete($user);
        return $result;
    }

}