<?php

namespace App\Http\Controllers\API;

use App\Company;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CompanyProfileResource;
use App\Http\Requests\UpdateCompanyProfileRequest;

/**
 * @OA\Schema(
 *     schema="Company",
 *     type="object",
 *     title="Company",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Flick Tech"),
 *     @OA\Property(property="logo", type="string", format="url", example="https://example.com/logo.png"),
 *     @OA\Property(property="sector", type="string", example="Technology"),
 *     @OA\Property(property="businessCategories", type="array", @OA\Items(type="string", example="Technology")),
 *     @OA\Property(property="state", type="string", example="Giza"),
 *     @OA\Property(property="city", type="string", example="Cairo"),
 *     @OA\Property(property="country", type="string", example="Egypt"),
 *     @OA\Property(property="address", type="string", example="123 Nile St."),
 *     @OA\Property(property="establishmentDate", type="string", format="date", example="2020-01-15"),
 *     @OA\Property(property="yearsOfOperations", type="integer", example=5),
 *     @OA\Property(property="productOrService", type="string", example="Mobile payment solution"),
 *     @OA\Property(property="infoCompany", type="string", example="Leading fintech startup."),
 *     @OA\Property(property="aboutProblem", type="string", example="Lack of financial inclusion."),
 *     @OA\Property(property="aboutMarketOpportunity", type="string", example="Large unbanked population."),
 *     @OA\Property(property="aboutCompetitors", type="string", example="PayMob, Fawry."),
 *     @OA\Property(property="aboutMonetization", type="string", example="Transaction fees and subscriptions."),
 *     @OA\Property(property="aboutChallenges", type="string", example="Regulatory compliance."),
 *     @OA\Property(property="idea", type="string", example="Build a secure fintech wallet."),
 *     @OA\Property(property="problem", type="string", example="People lack access to banking."),
 *     @OA\Property(property="solution", type="string", example="An inclusive digital wallet."),
 *     @OA\Property(property="teamNum", type="integer", example=12),
 *     @OA\Property(property="workingTeamSummary", type="string", example="Experienced fintech professionals."),
 *     @OA\Property(property="stage", type="object",
 *         @OA\Property(property="id", type="integer", example=3),
 *         @OA\Property(property="name", type="string", example="Growth")
 *     ),
 *     @OA\Property(property="fundingStage", type="string", example="Series A"),
 *     @OA\Property(property="revenueGenerating", type="boolean", example=true),
 *     @OA\Property(property="currentlyFundraising", type="boolean", example=false),
 *     @OA\Property(property="desiredFundingAmount", type="number", example=500000),
 *     @OA\Property(property="raisedFund", type="number", example=200000),
 *     @OA\Property(property="equityOffered", type="number", format="float", example=10.5),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="PreMoneyValuation", type="number", example=1500000),
 *     @OA\Property(
 *         property="targetedMarket",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Egypt")
 *         )
 *     ),
 *     @OA\Property(property="businessType", type="string", example="B2B"),
 *     @OA\Property(property="financialPlan", type="string", example="Detailed 3-year plan."),
 *     @OA\Property(property="financialPlanAttachment", type="string", format="url", example="https://example.com/plan.pdf"),
 *     @OA\Property(property="achievedMilestones", type="array", @OA\Items(
 *         @OA\Property(property="name", type="string", example="MVP Launch"),
 *         @OA\Property(property="description", type="string", example="Launched beta version.")
 *     )),
 *     @OA\Property(property="plannedMilestones", type="array", @OA\Items(
 *         @OA\Property(property="name", type="string", example="Scale to 3 countries"),
 *         @OA\Property(property="description", type="string", example="Expand operations.")
 *     )),
 *     @OA\Property(property="archivedMilestones", type="array", @OA\Items(
 *         @OA\Property(property="name", type="string", example="Seed Round Closed"),
 *         @OA\Property(property="description", type="string", example="Closed $200K round.")
 *     )),
 *     @OA\Property(property="Industries", type="array", @OA\Items(type="string", example="Fintech")),
 * 
 *     @OA\Property(
 *         property="founders",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="name", type="string", example="Ahmed Hassan"),
 *             @OA\Property(property="role", type="string", example="Founder"),
 *             @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *             @OA\Property(property="linkedin", type="string", example="https://linkedin.com/in/ahmed")
 *         )
 *     ),
 *     @OA\Property(
 *         property="shareholders",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="name", type="string", example="Sara Ali"),
 *             @OA\Property(property="role", type="string", example="Co-Founder"),
 *             @OA\Property(property="ownership", type="number", example=20)
 *         )
 *     ),
 *     @OA\Property(
 *         property="investors",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="name", type="string", example="XYZ Capital"),
 *             @OA\Property(property="investmentType", type="string", example="Angel Investor")
 *         )
 *     ),
 *     @OA\Property(
 *         property="sdgs",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="name", type="string", example="No Poverty")
 *         )
 *     ),
 *     @OA\Property(
 *         property="documents",
 *         type="array",
 *         @OA\Items(type="string", format="url", example="https://example.com/docs/file1.pdf")
 *     ),
 *     @OA\Property(property="pitchDeck", type="string", format="url", example="https://example.com/pitch.pdf"),
 *     @OA\Property(property="pitchDeckVideo", type="string", example="https://youtube.com/watch?v=abc123"),
 *     @OA\Property(property="businessPlan", type="string", format="url", example="https://example.com/business-plan.pdf"),
 *     @OA\Property(property="pitchDeckSummary", type="string", example="Our plan to reach 1M users."),
 *     @OA\Property(property="businessModel", type="string", example="Freemium + Subscription"),
 *     @OA\Property(property="businessModelDescription", type="string", example="Users start free, then upgrade."),
 *     @OA\Property(property="productPhoto", type="string", example="https://example.com/photo.png"),
 *     @OA\Property(property="CreatedTax", type="boolean", example=true),
 *     @OA\Property(property="CommercialRegister", type="string", example="123456"),
 *     @OA\Property(property="TaxCard", type="string", example="654321"),
 *     @OA\Property(
 *         property="countryRegister",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Egypt")
 *     ),
 *     @OA\Property(property="email", type="string", example="info@company.com"),
 *     @OA\Property(property="phone", type="string", example="+201234567890"),
 *     @OA\Property(property="countryCode", type="string", example="+20"),
 *     @OA\Property(property="whatsapp", type="string", example="1234567890"),
 *     @OA\Property(property="countryCodeWA", type="string", example="+20"),
 *     @OA\Property(property="googleMapUrl", type="string", format="url", example="https://goo.gl/maps/xyz"),
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
    if ($request->filled('programId')) {
        $query->where('cycle', $request->programId);
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
    if ($companies->isEmpty()) {
        return $this->error(__('api.companies_not_found'), 404);
    }

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
 /**
     * @OA\Post(
     *     path="/api/company/profile",
     *     summary="Update authenticated user's company profile",
     *     description="Updates the authenticated startup's profile with detailed business info, team, milestones, documents, etc.",
     *     operationId="updateCompany",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Flick Tech"),
     *                 @OA\Property(property="logo", type="string", format="binary"),
     *                 @OA\Property(property="country_id", type="integer", example=1),
     *                 @OA\Property(property="state_id", type="integer", example=2),
     *                 @OA\Property(property="sector_id", type="integer", example=3),
     *                 @OA\Property(property="stage_id", type="integer", example=4),
     *                 @OA\Property(property="categories[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="address", type="string", example="Smart Village"),
     *                 @OA\Property(property="yearsOfOperations", type="string", example="3"),
     *                 @OA\Property(property="productOrService", type="string", example="AI Platform for Startups"),
     *                 @OA\Property(property="email", type="string", format="email", example="info@flicktech.com"),
     *                 @OA\Property(property="phone", type="string", example="+20123456789"),
     *                 @OA\Property(property="countryCode", type="string", example="+20", description="Phone country code"),
     *                 @OA\Property(property="countryCodeWA", type="string", example="+20", description="WhatsApp country code"),
     *                 @OA\Property(property="whatsapp", type="string", example="+20123456789"),
     *                 @OA\Property(property="website", type="string", example="https://flicktech.com"),
     *                 @OA\Property(property="googleMapUrl", type="string", example="https://goo.gl/maps/xyz123"),
     *                 @OA\Property(property="linkedin", type="string", example="https://linkedin.com/company/flicktech"),
     *                 @OA\Property(property="x", type="string", example="https://x.com/flicktech"),
     *                 @OA\Property(property="facebook", type="string", example="https://facebook.com/flicktech"),
     *                 @OA\Property(property="youtube", type="string", example="https://youtube.com/@flicktech"),
     *                 @OA\Property(property="instagram", type="string", example="https://instagram.com/flicktech"),
     *                 @OA\Property(property="tiktok", type="string", example="https://tiktok.com/@flicktech"),
     *                 @OA\Property(property="aboutStartup", type="string"),
     *                 @OA\Property(property="aboutProblem", type="string"),
     *                 @OA\Property(property="aboutMarketOpportunities", type="string"),
     *                 @OA\Property(property="aboutCompetitors", type="string"),
     *                 @OA\Property(property="aboutMonetization", type="string"),
     *                 @OA\Property(property="aboutChallenges", type="string"),
     *                 @OA\Property(property="sustainabilityFeatures[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="currency", type="string", example="EGP"),
     *                 @OA\Property(property="preMoneyEvaluation", type="number", format="float", example=1000000),
     *                 @OA\Property(property="raisedFund", type="number", format="float", example=50000),
     *                 @OA\Property(property="fundingTarget", type="number", format="float", example=200000),
     *                 @OA\Property(property="targetMarket[]", type="array", @OA\Items(type="integer", example=1)),
     *                 @OA\Property(property="businessType", type="string"),
     *                 @OA\Property(property="financialPlanAttachment", type="string", format="binary"),
     *                 @OA\Property(property="teamNumber", type="integer", example=5),
     *                 @OA\Property(property="workingTeamDetails", type="string"),
     *                 @OA\Property(
     *                     property="founders[]",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string", example="Ahmed"),
     *                         @OA\Property(property="email", type="string", format="email"),
     *                         @OA\Property(property="linkedin", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="shareholders[]",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="role", type="string"),
     *                         @OA\Property(property="ownership", type="number")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="investors[]",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="investmentType", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="pitchDeckFile", type="string", format="binary"),
     *                 @OA\Property(property="pitchDeckVideoUrl", type="string", example="https://youtube.com/watch?v=xyz"),
     *                 @OA\Property(property="documents[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="businessModel", type="string"),
     *                 @OA\Property(property="businessModelDescription", type="string"),
     *                 @OA\Property(property="businessPlanAttachment", type="string", format="binary"),
     *                 @OA\Property(property="haveStartup", type="string", enum={"Yes", "No"}),
     *                 @OA\Property(property="CreatedTax", type="boolean"),
     *                 @OA\Property(property="CommercialRegister", type="string"),
     *                 @OA\Property(property="TaxCard", type="string"),
     *                 @OA\Property(property="Country_Register_id", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Company"),
     *             @OA\Property(property="message", type="string", example="Company profile updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
  public function update(UpdateCompanyProfileRequest $request)
{
        $user = auth()->user();
        if (!$user->ableTo('company.profile_update_mine')) {
          return $this->forbidden(__('api.forbidden'));
        }
        $company = Company::with(['milestones', 'teamMembers', 'state', 'city', 'country', 'stage', 'sector'])
        ->find($user->company_id);
        if(!$company) {
            return $this->notFound(__('api.company_not_found'));
        }

    DB::transaction(function () use ($request, $company, $user) {
        $data = $request->validated();

        if (isset($data['name'])) $company->{"name_en"} = $data['name'];
        if (isset($data['country_id'])) $company->country_id = $data['country_id'];
        if (array_key_exists('state_id', $data)) $company->state_id = $data['state_id'];
        if (array_key_exists('sector_id', $data)) $company->sector = $data['sector_id'];
        if (array_key_exists('stage_id', $data)) $company->stage = $data['stage_id'];
        if (isset($data['categories'])) $company->business_categories = json_encode($data['categories']);
        if (isset($data['address'])) $company->{"address_en"} = $data['address'];
        if (isset($data['yearsOfOperations'])) $company->years_of_operations = $data['yearsOfOperations'];
        if (isset($data['productOrService'])) $company->product_or_service = $data['productOrService'];
        if (isset($data['countryCode'])) {
            $company->country_code = $data['countryCode'];
        }
        if (isset($data['countryCodeWA'])) {
           $company->country_code_w_a = $data['countryCodeWA'];
        }
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $img_path = $image->store('images/logo', 'public_uploads');
            $img_url = Storage::disk('public_uploads')->url($img_path);
        } else {
         $img_url = $company->logo;
        }
        $company->logo = $img_url;

       foreach (['email', 'phone', 'whatsapp', 'website', 'googleMapUrl', 'linkedin', 'x', 'facebook', 'youtube', 'instagram', 'tiktok'] as $field) {
         if (isset($data[$field])) {
            $column = match ($field) {
             'x' => 'twitter',
             'googleMapUrl' => 'google_map',
             default => $field,
            };
            $company->{$column} = $data[$field];
         }
       }

        foreach (['aboutStartup' => 'info_company', 'aboutProblem' => 'about_problem', 'aboutMarketOpportunities' => 'about_market_opportunity', 'aboutCompetitors' => 'about_competitors', 'aboutMonetization' => 'about_monetize', 'aboutChallenges' => 'about_challenges'] as $req => $col) {
            if (isset($data[$req])) $company->{$col} = $data[$req];
        }
        if (isset($data['sustainabilityFeatures'])) {
            $company->Sustainable_development = json_encode($data['sustainabilityFeatures']);
        }

        foreach ([
            'currency' => 'Currency',
            'preMoneyEvaluation' => 'Pre_money_Valuation',
            'raisedFund' => 'raised_fund',
            'fundingTarget' => 'desired_funding_amount',
            'targetMarket' => 'targeted_market',
            'businessType' => 'business_type',
            'financialPlan' => 'financial_plan_description'
        ] as $req => $col) {
            if (isset($data[$req])) {
                $company->{$col} = in_array($req, ['targetMarket']) ? json_encode($data[$req]) : $data[$req];
            }
        }

        if (isset($data['financialPlanAttachment'])) {
            $file = $data['financialPlanAttachment'];
            $path = $file->storeAs(
                $user->company_id . '/financial_plan/documents',
                $file->getClientOriginalName(),
                'pitch_deck'
            );
            $company->financial_plan = 'financial_plan/' . $path;
        }

        if (isset($data['teamNumber'])) $company->team_num = $data['teamNumber'];
        if (isset($data['workingTeamDetails'])) $company->working_team_summary = $data['workingTeamDetails'];

        if (!empty($data['founders'])) {
            foreach ($data['founders'] as $founder) {
                $company->teamMembers()->create([
                    'Name' => $founder['name'],
                    'Email' => $founder['email'] ?? null,
                    'LinkedIn' => $founder['linkedin'] ?? null,
                    'TeamMember' => 'Founder',
                ]);
            }
        }

       if (!empty($data['shareholders'])) {
       foreach ($data['shareholders'] as $shareholder) {
         $company->teamMembers()->create([
            'Name' => $shareholder['name'],
            'RoleShereholder' => $shareholder['role'] ?? null,
            'PresentagShereholder' => $shareholder['ownership'],
            'TeamMember' => 'Shareholder'
         ]);
      }
    }
        if (!empty($data['investors'])) {
            foreach ($data['investors'] as $investor) {
                $company->teamMembers()->create([
                    'Name' => $investor['name'],
                    'investorType' => $investor['investmentType'] ?? null,
                    'TeamMember' => 'Investor'
                ]);
            }
        }

        foreach (['achievedMilestones', 'plannedMilestones', 'archivedMilestones'] as $type) {
            if (!empty($data[$type])) {
                foreach ($data[$type] as $milestone) {
                    $company->milestones()->create([
                        'plan' => ucfirst(str_replace('Milestones', '_Milestones', $type)),
                        'name' => $milestone['name'],
                        'description' => $milestone['description'],
                    ]);
                }
            }
        }

        if (isset($data['pitchDeckFile'])) {
            $file = $data['pitchDeckFile'];
            $path = $file->storeAs(
                $user->company_id . '/pitch_decks/documents',
                $file->getClientOriginalName(),
                'pitch_deck'
            );
            $company->pitch_deck = 'pitch_decks/' . $path;
        }

        if (!empty($data['documents'])) {
            $docs = json_decode($company->documents ?? '[]', true);
            foreach ($data['documents'] as $doc) {
                $path = $doc->storeAs(
                    $user->company_id . '/pitch_decks/documents',
                    $doc->getClientOriginalName(),
                    'pitch_deck'
                );
                $docs[] = 'pitch_decks/' . $path;
            }
            $company->documents = json_encode($docs);
        }

        if (isset($data['pitchDeckVideoUrl'])) {
            $company->pitch_deck_video = $data['pitchDeckVideoUrl'];
        }

        if (isset($data['businessModel'])) {
            $company->business_model = $data['businessModel'];
        }

        if (isset($data['businessModelDescription'])) {
            $company->business_model_description = $data['businessModelDescription'];
        }

        if (isset($data['businessPlanAttachment']) && $data['businessPlanAttachment']->isValid()) {
            $file = $data['businessPlanAttachment'];
            $originalName = $file->getClientOriginalName();

            $folder = match (true) {
                str_starts_with($file->getMimeType(), 'image/') => 'images',
                str_starts_with($file->getMimeType(), 'video/') => 'videos',
                in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'ppt', 'pptx']) => 'documents',
                default => 'others',
            };

            $path = $user->company_id . '/business_plan/' . $folder . '/';
            $file->storeAs($path, $originalName, ['disk' => 'pitch_deck']);
            $company->business_plan = 'business_plan/' . $path . $originalName;
        }

        if (isset($data['haveStartup'])) {
         if ($data['haveStartup'] === 'Yes') {
            $company->CreatedTax = $data['CreatedTax'] ?? 0;
            $company->Commercial_Register = $data['CommercialRegister'] ?? null;
            $company->Tax_Card = $data['TaxCard'] ?? null;
            $company->Country_Register_id = $data['Country_Register_id'] ?? null;
         } else {
            $company->CreatedTax = 0;
            $company->Commercial_Register = null;
            $company->Tax_Card = null;
            $company->Country_Register_id = null;
         }
        }
        if (isset($data['financialPlanAttachment'])) {
             $file = $data['financialPlanAttachment'];
            $path = $file->storeAs(
            $user->company_id . '/financial_plan/documents',
            $file->getClientOriginalName(),
            'pitch_deck'
            );
            $company->financial_plan = 'financial_plan/' . $path;
        }

           $company->save();
    });

        return $this->success(new CompanyProfileResource($company->fresh(['teamMembers', 'state', 'city', 'country', 'stage', 'sector'])),
            [],
            __('api.companyUpdated')
            );
}
}
