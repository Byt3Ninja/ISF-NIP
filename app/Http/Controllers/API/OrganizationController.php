<?php

namespace App\Http\Controllers\API;

use App\Role;
use App\User;
use App\Organization;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrganizationCreatedMail;
use App\Http\Resources\OrganizationResource;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
/**
 * @OA\Schema(
 *     schema="OrganizationResource",
 *     type="object",
 *     title="Organization Resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Tech Org"),
 *     @OA\Property(property="imgUrl", type="string", example="/uploads/logo.png"),
 *     @OA\Property(property="status", type="integer", example=1),
 *     @OA\Property(property="startups", type="integer", example=12),
 *     @OA\Property(property="cycles", type="integer", example=3),
 *     @OA\Property(property="user", type="object",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="Admin User"),
 *         @OA\Property(property="email", type="string", example="admin@org.com"),
 *         @OA\Property(property="phone", type="string", example="01012345678"),
 *         @OA\Property(property="imgUrl", type="string", example="/uploads/user.jpg"),
 *     )
 * )
 */
class OrganizationController extends Controller
{
    use ApiResponder;
   /**
 * @OA\Get(
 *     path="/api/organizations",
 *     summary="Get list of all organizations with full details",
 *     tags={"Organizations"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filter organizations by status (1 = active, 0 = inactive)",
 *         required=false,
 *         @OA\Schema(type="integer", enum={0, 1})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of organizations returned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Tech Org"),
 *                     @OA\Property(property="imgUrl", type="string", example="/uploads/logo.png"),
 *                     @OA\Property(property="status", type="integer", example=1),
 *                     @OA\Property(property="startups", type="integer", example=12),
 *                     @OA\Property(property="cycles", type="integer", example=3),
 *                     @OA\Property(property="user", type="object",
 *                         @OA\Property(property="id", type="integer", example=5),
 *                         @OA\Property(property="name", type="string", example="Admin User"),
 *                         @OA\Property(property="email", type="string", example="admin@org.com"),
 *                         @OA\Property(property="phone", type="string", example="01012345678"),
 *                         @OA\Property(property="imgUrl", type="string", example="/uploads/user.jpg"),
 *                     )
 *                 )
 *             ),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="totalOrganizations", type="integer", example=50),
 *                 @OA\Property(property="totalActiveOrganizations", type="integer", example=40),
 *                 @OA\Property(property="totalInactiveOrganizations", type="integer", example=10)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Only admins and org managers can access this endpoint",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     )
 * )
 */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->ableTo('organizations.index')) {
            return $this->forbidden(__('api.forbidden'));
        }

     $query = Organization::with(['user.company', 'cycles']);

     $statusFilterApplied = false;
     $status = null;

     if ($request->has('status')) {
        $status = (int) $request->get('status');
        if (in_array($status, [0, 1])) {
            $query->where('status', $status);
            $statusFilterApplied = true;
        }
     }

     $organizations = $query->get();

       if ($statusFilterApplied) {
         $totalOrganizations = Organization::where('status', $status)->count();
         $totalActive = $status == 1 ? $totalOrganizations : 0;
         $totalInactive = $status == 0 ? $totalOrganizations : 0;
      } else {
         $totalOrganizations = Organization::count();
         $totalActive = Organization::where('status', 1)->count();
         $totalInactive = Organization::where('status', 0)->count();
     }

       return $this->success(
            OrganizationResource::collection($organizations),
            [
                'meta' => [
                    'totalOrganizations' => $totalOrganizations,
                    'totalActiveOrganizations' => $totalActive,
                    'totalInactiveOrganizations' => $totalInactive,
                ]
            ]
        );
     }

     /**
 * @OA\Post(
 *     path="/api/organizations",
 *     summary="Create a new organization",
 *     tags={"Organizations"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","phone","password","firstName","lastName","gender","status"},
 *             @OA\Property(property="name", type="string", example="Tech Corp"),
 *             @OA\Property(property="email", type="string", example="admin@techcorp.com"),
 *             @OA\Property(property="phone", type="string", example="01012345678"),
 *             @OA\Property(property="password", type="string", example="SecurePass123"),
 *             @OA\Property(property="firstName", type="string", example="John"),
 *             @OA\Property(property="lastName", type="string", example="Doe"),
 *             @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *             @OA\Property(property="status", type="integer", enum={0,1}, example=1),
 *             @OA\Property(
 *                 property="imgUrl",
 *                 type="string",
 *                 format="binary"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Organization created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/OrganizationResource")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to create organization"
 *     )
 * )
 */
    public function store(StoreOrganizationRequest $request)
{
    $user = Auth::user();
    if (!$user->ableToAny(['organizations.create', 'organizations.store'])) {
        return $this->forbidden(__('api.forbidden'));
    }
    $data = $request->validated();
    $img_url = '/images/background/logo.png';

    try {
        if ($request->hasFile('imgUrl')) {
            $image = $request->file('imgUrl');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('organizations/'), $imageName);
            $img_url = 'organizations/' . $imageName;
        }
 
        $organization = DB::transaction(function () use ($data, $img_url, $request) {
            $password = $data['password'];

            $user = User::create([
                'first_name_en' => $data['firstName'],
                'last_name_en'  => $data['lastName'],
                'first_name_ar' => $data['firstName'],
                'last_name_ar'  => $data['lastName'],
                'email'         => $data['email'],
                'gender'        => $data['gender'],
                'phone'         => $data['phone'],
                'HaveCompany'   => 0,
                'password'      => bcrypt($password),
                'country_code' => $request->countryCode
            ]);

            $role = Role::firstOrCreate(
                ['default_for' => 'organizationManager'],
                ['name' => 'organizationManager', 'guard_name' => 'web']
            );

            $user->allotRole($role->default_for);

            $organization = new Organization();
            $organization->name_en = $data['name'];
            $organization->name_ar = $data['name'];
            $organization->img_url = $img_url;
            $organization->status = $data['status'];
            $organization->user_id = $user->id;
            $organization->save();

            $user->organization_id = $organization->id;
            $user->save();

            // Send email
            Mail::to($user->email)->queue(new OrganizationCreatedMail($organization, $user, $password));
            return $organization;
        });

        return $this->created(new OrganizationResource($organization), __('api.organizationCreated'), [], 201);

    } catch (\Throwable $e) {
        return $this->error(__('api.organizationCreationFailed'), 500);
    }
}

/**
 * @OA\Post(
 *     path="/api/organizations/{id}",
 *     summary="Update an existing organization",
 *     tags={"Organizations"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Organization ID",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Org"),
 *             @OA\Property(property="email", type="string", example="newemail@org.com"),
 *             @OA\Property(property="phone", type="string", example="01123456789"),
 *             @OA\Property(property="firstName", type="string", example="Jane"),
 *             @OA\Property(property="lastName", type="string", example="Smith"),
 *             @OA\Property(property="gender", type="string", enum={"male", "female"}, example="female"),
 *             @OA\Property(property="password", type="string", example="NewPassword123"),
 *             @OA\Property(property="status", type="integer", enum={0,1}, example=1),
 *             @OA\Property(
 *                 property="imgUrl",
 *                 type="string",
 *                 format="binary"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Organization updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/OrganizationResource")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update organization"
 *     )
 * )
 */
public function update(UpdateOrganizationRequest $request, $id)
{
    $user = Auth::user();
    if (!$user->ableToAny(['organizations.edit', 'organizations.update'])) {
        return $this->forbidden(__('api.forbidden'));
    }
    $organization = Organization::find($id);
    if (empty($organization)) {
        return $this->error(__('api.organizationNotFound'), 404);
    }
    $user = $organization->user;

    $data = $request->validated();
    $img_url = $organization->img_url ?? '/images/background/logo.png';

    try {
        if ($request->hasFile('imgUrl')) {
            $image = $request->file('imgUrl');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('organizations/'), $imageName);
            $img_url = 'organizations/' . $imageName;
        }

        DB::transaction(function () use ($organization, $user, $data, $img_url) {
            $organization->update([
                'name_en' => $data['name'] ?? $organization->name_en,
                'name_ar' => $data['name'] ?? $organization->name_ar,
                'img_url' => $img_url,
                'status' => $data['status'] ?? $organization->status,
            ]);

            $user->first_name_en = $data['firstName'] ?? $user->first_name_en;
            $user->last_name_en  = $data['lastName'] ?? $user->last_name_en;
            $user->first_name_ar = $data['firstName'] ?? $user->first_name_ar;
            $user->last_name_ar  = $data['lastName'] ?? $user->last_name_ar;
            $user->gender        = $data['gender'] ?? $user->gender;
            $user->phone         = $data['phone'] ?? $user->phone;
            $user->organization_id = $organization->id;
            $user->country_code   = $data['countryCode'] ?? $user->country_code;

            if (!empty($data['email']) && $data['email'] !== $user->email) {
                $user->email = $data['email'];
            }

            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }

            $user->save();
            return $organization;
        });
        $organization->refresh();
        return $this->success(new OrganizationResource($organization), [],__('api.organizationUpdated'));

    } catch (\Throwable $e) {
        return $this->error(__('api.organizationUpdateFailed'), 500);
    }
}

/**
 * @OA\Delete(
 *     path="/api/organizations/{id}",
 *     summary="Delete an organization (soft or force)",
 *     tags={"Organizations"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the organization to delete",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Organization deleted permanently",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Organization deleted successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Organization has dependencies and was soft deleted",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Organization cannot be deleted because it is linked to active cycles or startups.")
 *         )
 *     )
 * )
 */
public function destroy($id)
{
    $user = Auth::user();
    if (!$user->ableTo('organizations.destroy')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $organization = Organization::with(['cycles', 'cycles.companies'])->find($id);
    if (!$organization) {
        return $this->notFound(__('api.organizationNotFound'));
    }

    $hasCycles = $organization->cycles()->exists();
    $hasCompanies = $organization->cycles->flatMap->companies->isNotEmpty();

    if ($hasCycles || $hasCompanies) {
        $organization->delete();

        Log::warning('Organization soft deleted due to active dependencies.', [
            'organization_id' => $organization->id,
            'has_cycles' => $hasCycles,
            'has_companies' => $hasCompanies,
            'performed_by' => auth()->id(),
        ]);


        return $this->error(__('api.Organization cannot be deleted'), 400);
    }
      
    $organization->forceDelete();

    return $this->success(null, [],__('api.organizationDeleted'));
   
}

}
