<?php

namespace App\Http\Controllers\API;

use App\Company;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\CompanyService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\FounderResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\InvestorResource;
use App\Http\Resources\MilestoneResource;
use App\Http\Requests\StoreFounderRequest;
use App\Http\Requests\StoreInvestorRequest;
use App\Http\Requests\UpdateFounderRequest;
use App\Http\Resources\ShareholderResource;
use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateInvestorRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Http\Requests\StoreShareholderRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Http\Requests\UpdateShareholderRequest;
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
 *     @OA\Property(property="preMoneyValuation", type="number", example=1500000),
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
 *     @OA\Property(property="industries", type="array", @OA\Items(type="string", example="Fintech")),
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
 *     @OA\Property(property="createdTax", type="boolean", example=true),
 *     @OA\Property(property="commercialRegister", type="string", example="123456"),
 *     @OA\Property(property="taxCard", type="string", example="654321"),
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
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }
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
    $filters = $request->only(['sector', 'country', 'state', 'organization', 'programId', 'search', 'limit']);

    $companies = $this->companyService->getCompanies($filters, isApi: true);

    return $this->success(
        CompanyResource::collection($companies->items()),
        [
            'meta' => [
                'total' => $companies->total(),
                'page' => $companies->currentPage(),
                'limit' => (int) $request->get('limit', 10),
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
     *                 @OA\Property(property="createdTax", type="boolean"),
     *                 @OA\Property(property="commercialRegister", type="string"),
     *                 @OA\Property(property="taxCard", type="string"),
     *                 @OA\Property(property="country_Register_id", type="integer")
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
        if (isset($data['categories'])) {  
         $allCategories = Company::static_categories();
         $translated = [];

        foreach ($data['categories'] as $key) {
            $translationKey = match ($key) {
                'food_beverage' => __('admin.categories.food_beverage'),
                'technology' => __('admin.categories.technology'),
                'app_website' => __('admin.categories.app_website'),
                'housewares_home_design' => __('admin.categories.housewares_home_design'),
                'health_wellness_nutrition' => __('admin.categories.health_wellness_nutrition'),
                'fitness' => __('admin.categories.fitness'),
                'sports' => __('admin.categories.sports'),
                'beauty' => __('admin.categories.beauty'),
                'clothing_fashion' => __('admin.categories.clothing_fashion'),
                'toys_games' => __('admin.categories.toys_games'),
                'children' => __('admin.categories.children'),
                'entertainment_experiential' => __('admin.categories.entertainment_experiential'),
                'pets' => __('admin.categories.pets'),
                'music' => __('admin.categories.music'),
                'holiday' => __('admin.categories.holiday'),
                'other' => __('admin.categories.other'),
                 default => null,
                };

             if ($translationKey) {
                  $translated[] = $translationKey;
                }
            }
          $company->business_categories = json_encode($translated);
        }
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
            $company->CreatedTax = $data['createdTax'] ?? 0;
            $company->Commercial_Register = $data['commercialRegister'] ?? null;
            $company->Tax_Card = $data['taxCard'] ?? null;
            $company->Country_Register_id = $data['country_Register_id'] ?? null;
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
/**
 * @OA\Delete(
 *     path="/api/company/founder/{id}",
 *     summary="Delete a founder by ID",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Founder ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Founder deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Founder not found"
 *     )
 * )
 */
public function deleteFounder($id)
{ 
    $user =  auth()->user();
    if (!$user->ableTo('company.profile_teams_delete')) {
       return $this->forbidden(__('api.forbidden'));
    }
    $deleted = $this->companyService->deleteFounder($user->company_id, $id);

    return $deleted
        ? $this->success(null, [], __('api.founder deleted successfully'))
        : $this->notFound(__('api.founderNotFound'));
}
/**
 * @OA\Delete(
 *     path="/api/company/shareholder/{id}",
 *     summary="Delete a shareholder by ID",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Shareholder ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shareholder deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Shareholder not found"
 *     )
 * )
 */
public function deleteShareholder($id)
{  
    $user =  auth()->user();
    if (!$user->ableTo('company.profile_teams_delete')) {
       return $this->forbidden(__('api.forbidden'));
    }
    $deleted =  $this->companyService->deleteShareholder($user->company_id, $id);

    return $deleted
        ? $this->success(null, [], __('api.Shareholder deleted successfully'))
        : $this->notFound(__('api.ShareholderNotFound'));
}

/**
 * @OA\Delete(
 *     path="/api/company/investor/{id}",
 *     summary="Delete an investor by ID",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Investor ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Investor deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Investor not found"
 *     )
 * )
 */
public function deleteInvestor($id)
{
    $user =  auth()->user();
    if (!$user->ableTo('company.profile_teams_delete')) {
       return $this->forbidden(__('api.forbidden'));
    }
    $deleted =  $this->companyService->deleteInvestor($user->company_id, $id);

    return $deleted
        ? $this->success(null, [], __('api.investorDeleted'))
        : $this->notFound(__('api.investorNotFound'));
}
/**
 * @OA\Delete(
 *     path="/api/company/milestones/{id}",
 *     summary="Delete a specific milestone by ID",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Milestone ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Milestone deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Milestone not found"
 *     )
 * )
 */
public function deleteMilestone($id)
{
    $user = auth()->user();

    if (!$user->ableTo('company.profile_milestones_delete')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $milestone = $this->companyService->findMilestone($user->company_id, $id);

    if (!$milestone) {
        return $this->notFound(__('api.milestoneNotFound'));
    }

    $this->companyService->deleteMilestone($id);

    return $this->success(null, [], __('api.milestoneDeleted'));
}
/**
 * @OA\Post(
 *     path="/api/company/founder",
 *     summary="Create a founder",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "linkedin"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="linkedin", type="string", format="url", example="https://linkedin.com/in/johndoe")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Founder created successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function storeFounder(StoreFounderRequest $request)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_teams_add')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $founder = $this->companyService->createFounder($request->validated());
    return $this->created(new FounderResource($founder), __('api.founder_created'));
}

/**
 * @OA\Put(
 *     path="/api/company/founder/{id}",
 *     summary="Update a founder",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Founder ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Name"),
 *             @OA\Property(property="email", type="string", format="email", example="newemail@example.com"),
 *             @OA\Property(property="linkedin", type="string", format="url", example="https://linkedin.com/in/updated")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Founder updated successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function updateFounder(UpdateFounderRequest $request, $id)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_teams_edit')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $founder = $this->companyService->updateFounder($id, $request->validated());
    return $this->success(new FounderResource($founder),[], __('api.founder_updated'));
}

/**
 * @OA\Post(
 *     path="/api/company/shareholder",
 *     summary="Create a shareholder",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "ownership"},
 *             @OA\Property(property="name", type="string", example="Sarah Connor"),
 *             @OA\Property(property="role", type="string", example="Board Member"),
 *             @OA\Property(property="ownership", type="number", example=25)
 *         )
 *     ),
 *     @OA\Response(response=201, description="Shareholder created successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function storeShareholder(StoreShareholderRequest $request)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_teams_add')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $shareholder = $this->companyService->createShareholder($request->validated());
    return $this->created(new ShareholderResource($shareholder), __('api.shareholder_created'));
}

/**
 * @OA\Put(
 *     path="/api/company/shareholder/{id}",
 *     summary="Update a shareholder",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Shareholder ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Shareholder Name"),
 *             @OA\Property(property="role", type="string", example="New Role"),
 *             @OA\Property(property="ownership", type="number", example=15.5)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Shareholder updated successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

public function updateShareholder(UpdateShareholderRequest $request, $id)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_teams_edit')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $shareholder = $this->companyService->updateShareholder($id, $request->validated());
    return $this->success(new ShareholderResource($shareholder),[], __('api.shareholder_updated'));
}

/**
 * @OA\Post(
 *     path="/api/company/investor",
 *     summary="Create an investor",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "investmentType"},
 *             @OA\Property(property="name", type="string", example="Sequoia Capital"),
 *             @OA\Property(property="investmentType", type="string", example="Seed Funding")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Investor created successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function storeInvestor(StoreInvestorRequest $request)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_teams_add')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $investor = $this->companyService->createInvestor($request->validated());
    return $this->created(new InvestorResource($investor), __('api.investor_created'));
}

/**
 * @OA\Put(
 *     path="/api/company/investor/{id}",
 *     summary="Update an investor",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Investor ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated VC Name"),
 *             @OA\Property(property="investmentType", type="string", example="Series A")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Investor updated successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

public function updateInvestor(UpdateInvestorRequest $request, $id)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_teams_edit')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $investor = $this->companyService->updateInvestor($id, $request->validated());
    return $this->success(new InvestorResource($investor),[], __('api.investor_updated'));
}

/**
 * @OA\Post(
 *     path="/api/company/milestones",
 *     summary="Create a milestone",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"plan", "name"},
 *             @OA\Property(property="plan", type="string", enum={"Achieved_Milestones","Planned_Milestones","Archived_Milestones"}, example="Planned_Milestones"),
 *             @OA\Property(property="name", type="string", example="Product Launch Q1"),
 *             @OA\Property(property="description", type="string", example="Launching MVP to early users")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Milestone created successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function storeMilestone(StoreMilestoneRequest $request)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_milestones_add')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $milestone = $this->companyService->createMilestone($request->validated());
    return $this->created(new MilestoneResource($milestone), __('api.milestone_created'));
}

/**
 * @OA\Put(
 *     path="/api/company/milestones/{id}",
 *     summary="Update a milestone",
 *     tags={"Companies"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Milestone ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="plan", type="string", example="Achieved_Milestones"),
 *             @OA\Property(property="name", type="string", example="Updated Milestone Title"),
 *             @OA\Property(property="description", type="string", example="Updated description of the milestone")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Milestone updated successfully"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function updateMilestone(UpdateMilestoneRequest $request, $id)
{
    $user = auth()->user();
    if (!$user->ableTo('company.profile_milestones_edit')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $milestone = $this->companyService->updateMilestone($id, $request->validated());
    return $this->success(new MilestoneResource($milestone),[], __('api.milestone_updated'));
}

}
