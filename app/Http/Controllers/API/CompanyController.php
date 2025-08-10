<?php

namespace App\Http\Controllers\API;

use App\Company;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CompanyProfileResource;

 /**
 * @OA\Schema(
 *     schema="Company",
 *     type="object",
 *     title="Company",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="logo", type="string", format="url", example="https://example.com/logo.png"),
 *     @OA\Property(property="name", type="string", example="Flick Tech"),
 *     @OA\Property(property="sector", type="string", example="Technology"),
 *     @OA\Property(property="establishmentDate", type="string", format="date", example="2020-01-15"),
 *     @OA\Property(property="state", type="string", example="Giza"),
 *     @OA\Property(property="city", type="string", example="Cairo"),
 *     @OA\Property(property="country", type="string", example="Egypt"),
 * 
 *     @OA\Property(
 *         property="organization",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=12),
 *         @OA\Property(property="name", type="string", example="Flat6Labs")
 *     ),
 *     @OA\Property(
 *         property="founders",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="name", type="string", example="Ahmed Hassan"),
 *             @OA\Property(property="role", type="string", example="Founder")
 *         )
 *     ),
 * 
 *     @OA\Property(
 *         property="sdgs",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="No Poverty")
 *         )
 *     ),
 * 
 *     @OA\Property(
 *         property="socialLinks",
 *         type="object",
 *         @OA\Property(property="website", type="string", format="url", example="https://company.com"),
 *         @OA\Property(property="linkedin", type="string", format="url", example="https://linkedin.com/company"),
 *         @OA\Property(property="x", type="string", format="url", example="https://x.com/company"),
 *         @OA\Property(property="facebook", type="string", format="url", example="https://facebook.com/company"),
 *         @OA\Property(property="youtube", type="string", format="url", example="https://youtube.com/company"),
 *         @OA\Property(property="instagram", type="string", format="url", example="https://instagram.com/company"),
 *         @OA\Property(property="tiktok", type="string", format="url", example="https://tiktok.com/@company")
 *     )
 * )
 */
class CompanyController extends Controller
{
    use ApiResponder;
    /**
 * @OA\Get(
 *     path="/api/startups",
 *     summary="List all companies (startups)",
 *     description="Returns a list of companies with optional filters. Admin can view all; Organization Managers see companies in their organization via cycle relation.",
 *     operationId="getCompanies",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     
 *     @OA\Parameter(
 *         name="sector",
 *         in="query",
 *         description="Filter by sector ID or name",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="country",
 *         in="query",
 *         description="Filter by country name",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="state",
 *         in="query",
 *         description="Filter by state name",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search by company name, sector, city, or team member",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         description="Number of results per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="List of companies",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Company")),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="total", type="integer", example=50),
 *                 @OA\Property(property="page", type="integer", example=1),
 *                 @OA\Property(property="limit", type="integer", example=10)
 *             )
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - user does not have permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="You are not authorized to access this resource.")
 *         )
 *     )
 * )
 */

    public function index(Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('users.view_companies')) {
       return $this->forbidden(__('api.forbidden'));
    }

    $locale = app()->getLocale();
    $query = Company::query()->with(['teamMembers', 'country', 'state', 'city', 'cycleRelation.organization']);

    if ($user->hasRole('organizationManager')) {
        $organizationId = $user->organization_id;
        $query->whereHas('cycleRelation.organization', function ($q) use ($organizationId) {
            $q->where('id', $organizationId);
        });
    }
    
    if ($request->filled('sector')) {
        if (is_numeric($request->sector)) {
            $query->where('sector', $request->sector);
        } else {
            $query->whereHas('sectoR', function ($q) use ($request, $locale) {
                $q->where("name_{$locale}", 'like', "%{$request->sector}%");
            });
        }
    }

    if ($request->filled('country')) {
    $query->whereHas('country', function ($q) use ($request, $locale) {
        $q->where("name_{$locale}", 'like', "%{$request->country}%");
    });
    }

    if ($request->filled('organization')) {
    $query->whereHas('cycleRelation.organization', function ($q) use ($request, $locale) {
        if (is_numeric($request->organization)) {
            $q->where('id', $request->organization);
        } else {
            $q->where("name_{$locale}", 'like', "%{$request->organization}%");
        }
    });
}

    if ($request->filled('state')) {
            $query->whereHas('state', function ($q) use ($request, $locale) {
                $q->where("name_{$locale}", 'like', "%{$request->state}%");
            });
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($sub) use ($search, $locale) {
            $sub->where("name_{$locale}", 'like', "%{$search}%")
                ->orWhereHas('sectoR', function ($q) use ($search, $locale) {
                    $q->where("name_{$locale}", 'like', "%{$search}%");
                })
               ->orWhereHas('city', function ($q) use ($search, $locale) {
                    $q->where("name_{$locale}", 'like', "%{$search}%");
                })
                ->orWhereHas('teamMembers', function ($q) use ($search) {
                    $q->where('Name', 'like', "%{$search}%");
                });
        });
    }

    $limit = $request->get('limit', 10);
    $companies = $query->paginate($limit);

    return $this->success(
        CompanyResource::collection($companies->items()),
        [
            'meta' => [
                'total' => $companies->total(),
                'page' => $companies->currentPage(),
                'limit' => (int) $limit            
            ],
            'links' => [
            'first' => $companies->url(1),
            'last' => $companies->url($companies->lastPage()),
            'prev' => $companies->previousPageUrl(),
            'next' => $companies->nextPageUrl(),
        ],
        ]
    );
}
/**
 * @OA\Get(
 *     path="/api/startups/{id}",
 *     summary="Get startup (company) profile",
 *     description="Returns detailed profile of a specific startup. Access controlled by role: Admin can access all, OrganizationManager startups under their org, Registered only their own.",
 *     operationId="getStartupProfile",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the startup (company)",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Startup profile fetched successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/Company")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - user not allowed to access this startup",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="You are not authorized to access this resource.")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=404,
 *         description="Company not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Company not found.")
 *         )
 *     )
 * )
 */
public function show($id)
{
    $user = auth()->user();
     if (!$user->ableToAny(['company.profile_view', 'company.profile_view_mine'])) {
        return $this->forbidden(__('api.forbidden'));
    }
    $company = Company::with([
        'country', 'state', 'city', 'teamMembers', 'sectoR'
    ])->find($id);
    
    if (!$company) {
        return $this->error(__('api.company_not_found'), 404);
    }

    if ($user->hasAnyRole(['Admin', 'organizationManager'])) {
        return $this->success(new CompanyProfileResource($company));
    }

    if ($user->hasRole('Registered') && $user->company_id == $company->id) {
        return $this->success(new CompanyProfileResource($company));
    }

     return $this->forbidden(__('api.forbidden'));
}
}
