<?php

namespace App\Http\Controllers\API;

use App\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrganizationResource;
use App\Traits\ApiResponder;
use Symfony\Component\HttpFoundation\Response;

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
 *         description="Forbidden - Only admins can access this endpoint",
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
            return response()->json([
                'success' => false,
                'message' => __('api.forbidden'),
            ], Response::HTTP_FORBIDDEN);
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
}
