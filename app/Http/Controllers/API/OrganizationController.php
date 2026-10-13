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
use App\Services\OrganizationService;
use App\Http\Resources\OrganizationResource;
use App\Repositories\OrganizationRepository;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Services\FileManagerService;
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
    public function __construct(
        protected OrganizationService $organizationService,
        protected OrganizationRepository $organizationRepo,
    ) {}
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
        $this->authorize('viewAny', Organization::class);
        $status = $request->get('status');
        $data = $this->organizationService->listOrganizations($status);

        return $this->success(
            OrganizationResource::collection($data['organizations']),
            ['meta' => $data['counts']]
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
    $this->authorize('create', Organization::class);
    $img_url = '/images/background/logo.png';
        if ($request->hasFile('imgUrl')) {
            $file = $request->file('imgUrl');
            // Stored name is derived server-side from validated content, never from the client extension.
            $safeName = app(FileManagerService::class)->validatedSafeName($file, 'image');
            $file->move(public_path('organizations'), $safeName);
            $img_url = 'organizations/' . $safeName;
        }
        $org = $this->organizationService->createOrganization(
            $request->validated(),
            $img_url,
            $request->countryCode
        );
        return $this->created(new OrganizationResource($org), __('api.organizationCreated'));
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
    $org = $this->organizationRepo->find($id);
    if (!$org) return $this->notFound(__('api.organizationNotFound'));
    $this->authorize('update', $org);

    $org = $this->organizationService->updateOrganization($org,$request->validated(),$request->file('imgUrl'));
    return $this->success(new OrganizationResource($org),[], __('api.organizationUpdated'));
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
   $org = $this->organizationRepo->findWithCycles($id);
   if (!$org) return $this->notFound(__('api.organizationNotFound'));
   $this->authorize('delete', $org);
   $result = $this->organizationService->deleteOrganization($org);
   if ($result === true) {
        return $this->success(null, [], __('api.organizationDeleted'));
    }
    return $this->error(__('api.Organization cannot be deleted'), 400); 
}

}
