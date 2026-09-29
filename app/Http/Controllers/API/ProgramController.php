<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Messenger;
use Carbon\Carbon;
use App\ScreeningEvaluation;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\ProgramService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ProgramResource;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Http\Requests\GetProgramMentorsRequest;
use App\Http\Requests\GetProgramStartupsRequest;
use App\{Cycle, Sector, Stage, ScreeningEvaluationResults, SurveyResponses};

/**
 * @OA\Schema(
 *     schema="Program",
 *     type="object",
 *     title="Program",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Startup Incubator 2025"),
 *     @OA\Property(property="description", type="string", example="A program for startups"),
 *     @OA\Property(property="start", type="string", format="date", example="2025-08-01"),
 *     @OA\Property(property="end", type="string", format="date", example="2025-12-01"),
 *     @OA\Property(property="type", type="string", example="incubation"),
 *     @OA\Property(
 *         property="organization",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=3),
 *         @OA\Property(property="name", type="string", example="Cairo Tech Hub")
 *     ),
 *     @OA\Property(property="imgUrl", type="string", format="url", example="https://example.com/img.png"),
 *     @OA\Property(property="status", type="string", example="In Progress"),
 *     @OA\Property(property="progress", type="number", format="float", example=45.67),
 *     @OA\Property(property="startupCount", type="integer", example=12),
 *     @OA\Property(property="stepCount", type="integer", example=6),
 *     @OA\Property(property="mentorCount", type="integer", example=4),
 *     @OA\Property(property="pendingCount", type="integer", example=3),
 *     @OA\Property(property="responsibleUsers", type="array", @OA\Items(
 *         type="object",
 *         @OA\Property(property="initials", type="string", example="John D"),
 *         @OA\Property(property="userId", type="integer", example=5)
 *     )),
 *     @OA\Property(property="budgetAllocation", type="number", format="float", example=10000.00),
 *     @OA\Property(property="currencyType", type="string", example="USD"),
 *     @OA\Property(property="programGuideline", type="string", example="Follow company policies."),
 *     @OA\Property(property="financialGuideline", type="string", example="Budget rules"),
 *     @OA\Property(property="legalGuideline", type="string", example="Legal compliance details"),
 *     @OA\Property(property="allowNonCompany", type="boolean", example=true),
 *     @OA\Property(property="autoApproveMentorshipRequests", type="boolean", example=true),
 *     @OA\Property(property="private", type="boolean", example=false),
 *     @OA\Property(property="programGuidelineFile", type="string", format="url", example="https://example.com/policies/program.pdf"),
 *     @OA\Property(property="financialPolicyPdf", type="string", format="url", example="https://example.com/policies/finance.pdf"),
 *     @OA\Property(property="legalPolicyPdf", type="string", format="url", example="https://example.com/policies/legal.pdf"),
 *     @OA\Property(property="delegationPdf", type="string", format="url", example="https://example.com/policies/delegation.pdf")
 * )
 */
class ProgramController extends Controller
{
    use ApiResponder;

    protected $programService;
    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }
   /**
 * @OA\Post(
 *     path="/api/programs",
 *     operationId="storeProgram",
 *     tags={"Programs"},
 *     summary="Create a new program",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"organizationId", "type", "title", "start", "end"},
 *                 @OA\Property(property="organization", type="integer", example=1),
 *                 @OA\Property(property="type", type="string", example="incubation"),
 *                 @OA\Property(property="title", type="string", example="Startup Incubator 2025"),
 *                 @OA\Property(property="description", type="string", example="A program for early-stage startups"),
 *                 @OA\Property(property="start", type="string", format="date", example="2025-08-01"),
 *                 @OA\Property(property="end", type="string", format="date", example="2025-12-01"),
 *                 @OA\Property(property="private", type="boolean", example=true),
 *                 @OA\Property(property="allowNonCompany", type="boolean", example=true),
 *                 @OA\Property(property="budgetAllocation", type="number", format="float", example=10000.00),
 *                 @OA\Property(property="currencyType", type="string", example="USD"),
 *                 @OA\Property(property="autoApproveMentorshipRequests", type="boolean", example=true),
 *                 @OA\Property(property="programGuideline", type="string", example="Follow company policies."),
 *                 @OA\Property(property="financialGuideline", type="string", example="Financial rules and regulations."),
 *                 @OA\Property(property="legalGuideline", type="string", example="Legal instructions here."),
 *                 @OA\Property(property="programGuidelineFile", type="string", format="binary"),
 *                 @OA\Property(property="financialPolicyPdf", type="string", format="binary"),
 *                 @OA\Property(property="legalPolicyPdf", type="string", format="binary"),
 *                 @OA\Property(property="delegationPdf", type="string", format="binary"),
 *                 @OA\Property(property="imgUrl", type="string", format="binary"),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Program created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", example={"id": 1, "title": "Startup Incubator 2025"})
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 additionalProperties={"type": "array", "items": {"type": "string"}}
 *             )
 *         )
 *     )
 * )
 */
    public function store(StoreProgramRequest $request)
    {
        $user = Auth::user();
          if (!$user->ableToAny(['cycles.modify', 'programlist.create'])) {
            return $this->forbidden(__('api.forbidden'));
        }
        
       $validated = $request->validated();
       $response = DB::transaction(function () use ($request, $validated) {
            $cycle = new Cycle();
            $cycle->organization_id = $validated['organizationId'];
            $cycle->type = $validated['type'];
            $cycle->title = $validated['title'];
            $cycle->description = $validated['description'];
            $cycle->start = $validated['start'];
            $cycle->end = $validated['end'];
            $cycle->private = $request->private ? sha1(now()) : null;
            $cycle->allow_non_company = $request->has('allowNonCompany') ? 1 : 0;
            $cycle->budget_allocation = $request->budgetAllocation;
            $cycle->currency_type = $request->currencyType;
            $cycle->auto_approve_mentorship_requests = $request->has('autoApproveMentorshipRequests') ? 1 : 0;
            $cycle->program_guideline = $request->programGuideline;
            $cycle->financial_guideline = $request->financialGuideline;
            $cycle->legal_guideline = $request->legalGuideline;

            $fileManager = app(\App\Services\FileManagerService::class);

            if ($request->hasFile('programGuidelineFile')) {
                $cycle->program_guideline_file = $fileManager->upload($request->file('programGuidelineFile'), 'programs', 'public_uploads');
            }

            if ($request->hasFile('financialPolicyPdf')) {
                $cycle->financial_guideline_file = $fileManager->upload($request->file('financialPolicyPdf'), 'programs', 'public_uploads');
            }

            if ($request->hasFile('legalPolicyPdf')) {
                $cycle->legal_guideline_file = $fileManager->upload($request->file('legalPolicyPdf'), 'programs', 'public_uploads');
            }

            if ($request->hasFile('delegationPdf')) {
                $cycle->delegation_file = $fileManager->upload($request->file('delegationPdf'), 'programs', 'public_uploads');
            }

            if ($request->hasFile('imgUrl')) {
                $cycle->img_url = $fileManager->upload($request->file('imgUrl'), 'programs', 'public_uploads');
            }
            if (auth()->user()->hasRole('OrganizationManager')) {

                    $admin = User::role('Admin')->orderBy('id')->first();

                    if ($admin) {

                        \Illuminate\Support\Facades\Notification::route('mail', $admin->email)
                            ->notify(new \App\Notifications\ProgramCreatedNotification($cycle, $admin));

                        $messenger = new Messenger();
                        $messenger->sender = auth()->id();
                        $messenger->receiver = $admin->id;
                        $messenger->subject = 'New Program Created';
                        $messenger->message = "A new Program Titled \"{$cycle->title}\" has been created.";
                        $messenger->read = 0;
                        $messenger->save();
                    }
            }

            $cycle->save();
            return $this->created(new ProgramResource($cycle),__('api.programCreated'),[], 201);
        });
        return $response;

    }

/**
 * @OA\Post(
 *     path="/api/programs/{id}",
 *     operationId="updateProgram",
 *     tags={"Programs"},
 *     summary="Update an existing program",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="organizationId", type="integer", example=1),
 *                 @OA\Property(property="type", type="string", example="acceleration"),
 *                 @OA\Property(property="title", type="string", example="Startup Acceleration 2025"),
 *                 @OA\Property(property="description", type="string", example="Updated program description"),
 *                 @OA\Property(property="start", type="string", format="date", example="2025-09-01"),
 *                 @OA\Property(property="end", type="string", format="date", example="2026-01-01"),
 *                 @OA\Property(property="private", type="boolean", example=false),
 *                 @OA\Property(property="allowNonCompany", type="boolean", example=false),
 *                 @OA\Property(property="budgetAllocation", type="number", format="float", example=15000.00),
 *                 @OA\Property(property="currencyType", type="string", example="EUR"),
 *                 @OA\Property(property="autoApproveMentorshipRequests", type="boolean", example=false),
 *                 @OA\Property(property="programGuideline", type="string", example="Updated guideline..."),
 *                 @OA\Property(property="financialGuideline", type="string", example="Updated financial info..."),
 *                 @OA\Property(property="legalGuideline", type="string", example="Updated legal policy..."),
 *                 @OA\Property(property="programGuidelineFile", type="string", format="binary"),
 *                 @OA\Property(property="financialPolicyPdf", type="string", format="binary"),
 *                 @OA\Property(property="legalPolicyPdf", type="string", format="binary"),
 *                 @OA\Property(property="delegationPdf", type="string", format="binary"),
 *                 @OA\Property(property="imgUrl", type="string", format="binary"),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Program updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", example={"id": 1, "title": "Startup Acceleration 2025"})
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Program not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 additionalProperties={"type": "array", "items": {"type": "string"}}
 *             )
 *         )
 *     )
 * )
 */
  public function update(UpdateProgramRequest $request, $id)
{
      $user = Auth::user();
          if (!$user->ableToAny(['cycles.modify', 'programlist.edit'])) {
            return $this->forbidden(__('api.forbidden'));
        }
    $validated = $request->validated();

    $cycle = Cycle::find($id);

    if (!$cycle) {
        return $this->notFound(__('api.program_not_found'));
    }

     if (isset($validated['organizationId'])) {
        $cycle->organization_id = $validated['organizationId'];
    }

    if (isset($validated['title'])) {
        $cycle->title = $validated['title'];
    }

    if (isset($validated['description'])) {
        $cycle->description = $validated['description'];
    }

    if (isset($validated['start'])) {
        $cycle->start = $validated['start'];
    }

    if (isset($validated['end'])) {
        $cycle->end = $validated['end'];
    }

    if (array_key_exists('allowNonCompany', $validated)) {
        $cycle->allow_non_company = $validated['allowNonCompany'];
    }

    if (array_key_exists('private', $validated)) {
        $cycle->private = $validated['private'] ? sha1(now()) : null;
    }

    if (isset($validated['budgetAllocation'])) {
        $cycle->budget_allocation = $validated['budgetAllocation'];
    }

    if (isset($validated['currencyType'])) {
        $cycle->currency_type = $validated['currencyType'];
    }

    if (isset($validated['programGuideline'])) {
        $cycle->program_guideline = $validated['programGuideline'];
    }

    if (isset($validated['financialGuideline'])) {
        $cycle->financial_guideline = $validated['financialGuideline'];
    }

    if (isset($validated['legalGuideline'])) {
        $cycle->legal_guideline = $validated['legalGuideline'];
    }

    if (array_key_exists('autoApproveMentorshipRequests', $validated)) {
        $cycle->auto_approve_mentorship_requests = $validated['autoApproveMentorshipRequests'];
    }

    $fileManager = app(\App\Services\FileManagerService::class);

    if (isset($validated['imgUrl'])) {
        $cycle->img_url = $fileManager->update($validated['imgUrl'], $cycle->img_url, 'programs', 'public_uploads');
    }

    if (isset($validated['programGuidelineFile'])) {
        $cycle->program_guideline_file = $fileManager->update($validated['programGuidelineFile'], $cycle->program_guideline_file, 'programs', 'public_uploads');
    }

    if (isset($validated['financialPolicyPdf'])) {
        $cycle->financial_guideline_file = $fileManager->update($validated['financialPolicyPdf'], $cycle->financial_guideline_file, 'programs', 'public_uploads');
    }

    if (isset($validated['legalPolicyPdf'])) {
        $cycle->legal_guideline_file = $fileManager->update($validated['legalPolicyPdf'], $cycle->legal_guideline_file, 'programs', 'public_uploads');
    }

    if (isset($validated['delegationPdf'])) {
        $cycle->delegation_file = $fileManager->update($validated['delegationPdf'], $cycle->delegation_file, 'programs', 'public_uploads');
    }

    $cycle->save();
    $cycle->refresh();
    return $this->success(new ProgramResource($cycle), [], __('api.programUpdated'));

}
/**
 * @OA\Get(
 *     path="/api/v1/programs",
 *     operationId="getAllPrograms",
 *     tags={"Programs"},
 *     summary="Get list of programs",
 *     description="Retrieve all programs. Admin sees all, OrganizationManager sees only their organization programs.",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter by status: Upcoming, In Progress, Completed",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="program_type",
 *         in="query",
 *         required=false,
 *         description="Filter by program type (e.g., incubation, acceleration)",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="organization",
 *         in="query",
 *         required=false,
 *         description="Search organization by localized name",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         required=false,
 *         description="Search in program title and description",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         description="Number of records per page",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Program")),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="total", type="integer", example=100),
 *                 @OA\Property(property="page", type="integer", example=1),
 *                 @OA\Property(property="limit", type="integer", example=10)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden – user lacks permission"
 *     )
 * )
 */
public function index(Request $request)
{
       $user = Auth::user();
       if (!$user->ableTo('programlist.index')) {
         return $this->forbidden(__('api.forbidden'));
       }
 
      $locale = app()->getLocale();
      $query = Cycle::with(['organization', 'steps', 'mentors', 'companies']);

      $orgIds = $user->organizations_ids;
      if (!$user->hasRole('admin') && !empty($orgIds)) {
          $query->whereIn('organization_id', $orgIds);
       }

        if ($request->filled('status')) {
         $now = Carbon::now();
         $status = $request->status;

        $query->where(function ($q) use ($status, $now) {
         if ($status === 'Upcoming') {
            $q->whereDate('start', '>', $now);
         } elseif ($status === 'In Progress') {
            $q->whereDate('start', '<=', $now)
              ->whereDate('end', '>=', $now);
         } elseif ($status === 'Completed') {
            $q->whereDate('end', '<', $now);
         }
         });
      }

        if ($request->filled('program_type')) {
            $query->where('type', $request->program_type);
        }

       if ($request->filled('organization')) {
        $query->whereHas('organization', function ($q) use ($request, $locale) {
            $q->where('name_' . $locale, 'like', '%' . $request->organization . '%');
         });
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->boolean('hasSteps')) {
          $query->whereHas('steps');
        }
        $programs = $query->latest()->paginate($request->get('limit', 10));

    return $this->success(
    ProgramResource::collection($programs->items()),
    [
        'meta' => [
            'total' => $programs->total(),
            'page' => $programs->currentPage(),
            'limit' => $programs->perPage(),
        ],
        'links' => [
            'first' => $programs->url(1),
            'last' => $programs->url($programs->lastPage()),
            'prev' => $programs->previousPageUrl(),
            'next' => $programs->nextPageUrl(),
        ],
    ]
  );
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{id}",
 *     operationId="getProgramById",
 *     tags={"Programs"},
 *     summary="Get a specific program by ID",
 *     description="Returns program details including organization, mentors, steps, and related files.",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Program")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Program not found"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden – user lacks permission"
 *     )
 * )
 */
public function show($id)
{
    $user = Auth::user();

    if (!$user->ableTo('programlist.show')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $program = Cycle::with(['organization', 'steps', 'mentors', 'companies'])->find($id);

    if (!$program) {
        return $this->notFound(__('api.program_not_found'));
    }

    return $this->success(new ProgramResource($program));
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{programId}/summary",
 *     operationId="getProgramSummary",
 *     tags={"Programs"},
 *     summary="Get summary report for a program",
 *     description="Returns KPIs, evaluation completion, average score, and step progress.",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Summary data returned",
 *         @OA\JsonContent(
 *             @OA\Property(property="kpis", type="object"),
 *             @OA\Property(property="programInfo", type="object"),
 *             @OA\Property(property="programStages", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="finalStatusDistribution", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Program not found")
 * )
 */

public function summary(string $programId)
{
    $user = auth()->user();
    if (!$user->ableTo('programs.report')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $cycle = Cycle::withCount(['companies', 'mentors'])
        ->with(['steps' => fn($q) => $q->orderBy('arr')])
        ->find($programId);
    if(!$cycle) {
        return $this->notFound(__('api.program_not_found'));
    }

    $finalStepIds = $cycle->steps->where('stype', 5)->pluck('id');
    $allCompaniesData = collect();

    foreach ($finalStepIds as $stepId) {
        $meta = ScreeningEvaluationResults::metadata($stepId);
        if (!empty($meta['_companies'])) {
            foreach ($meta['_companies'] as $company) {
                $totalScore = 0; $count = 0;
                foreach ($meta['judges'] as $judge) {
                    $scoped = collect($judge->companies)->firstWhere('id', $company->id);
                    if ($scoped && strtolower($scoped->status) === 'published' && isset($scoped->score)) {
                        $totalScore += (float) $scoped->score; $count++;
                    }
                }
                $company->score = $count > 0 ? round($totalScore / $count, 1) : 0;
                $company->evaluation_status = $count > 0 ? 'published' : 'not_evaluated';
                $allCompaniesData->push($company);
            }
        }
    }

    $companyEvaluations = $allCompaniesData->groupBy('id')->map(fn($group) => $group->sortByDesc('score')->first())->values();
    $publishedCompanies = $companyEvaluations->where('evaluation_status', 'published')->values();
    $evaluationCompletion = $companyEvaluations->count() > 0 ? round($publishedCompanies->count() / $companyEvaluations->count() * 100, 2) : 0;
    $averageFinalScore = round($publishedCompanies->avg('score') ?? 0, 2);

    $companyEvaluations = $companyEvaluations->map(function ($company) {
        $company->status = match (true) {
            $company->evaluation_status === 'not_evaluated' => 'Not Evaluated',
            $company->score >= 90 => 'Highest Rated',
            $company->score >= 70 => 'Good Performance',
            default => 'Needs Improvement',
        };
        return $company;
    });

   $statusMap = [
     'Highest Rated' => [
        'en' => 'Highest Rated',
        'ar' => 'الأعلى تقييماً',
     ],
      'Good Performance' => [
        'en' => 'Good Performance',
        'ar' => 'أداء جيد',
      ],
     'Needs Improvement' => [
        'en' => 'Needs Improvement',
        'ar' => 'بحاجة لتحسين',
      ],
    ];
    $locale = app()->getLocale();
    $currentStepId = collect($cycle->companies->pluck('step')->filter()->mode())->first();
    $currentStep = $cycle->steps->firstWhere('id', $currentStepId);
    $statusDistribution = collect($statusMap)->map(function ($names, $statusKey) use ($companyEvaluations, $locale) {
    return [
        'status' => $names[$locale] ?? $names['en'],
        'count' => $companyEvaluations->where('status', $statusKey)->count(),
    ];
   })->values();

    return $this->success([
        'kpis' => [
            'totalCompanies' => $cycle->companies_count,
            'totalMentors' => $cycle->mentors_count,
            'totalWorkshops' => $cycle->trainingSessions()->count(),
            'averageFinalScore' => $averageFinalScore,
            'evaluationsComplete' => $evaluationCompletion,
        ],
        'programInfo' => [
            'title' => $cycle->title,
            'description' => $cycle->description,
            'currentStage' => $currentStep->id
        ],
        'programStages' => $cycle->steps->map(function ($step) use ($currentStep) {
         $now = Carbon::now();
         if ($step->from && $step->to) {
         if ($now->lt($step->from)) {
            $status = 'pending';
          } elseif ($now->between($step->from, $step->to)) {
            $status = 'in Progress';
          } else {
            $status = 'Completed';
        }
       } else {
        $status = 'Pending';
       }

     return [
        'id' => $step->id,
        'title' => $step->title,
        'status' => $status,
        'isCurrent' => $currentStep && $step->id === $currentStep->id,
        ];
      }),
        'finalStatusDistribution' => $statusDistribution
    ]);
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{programId}/performance",
 *     operationId="getProgramPerformance",
 *     tags={"Programs"},
 *     summary="Get company performance by sector and stage",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="sector_id",
 *         in="query",
 *         description="Filter by sector ID",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="stage_id",
 *         in="query",
 *         description="Filter by stage ID",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Performance data by sector and stage",
 *         @OA\JsonContent(
 *             @OA\Property(property="filters", type="object"),
 *             @OA\Property(property="companies", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="chartData", type="object")
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Program not found")
 * )
 */

public function performance(string $programId, Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('programs.report')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $cycle = Cycle::find($programId);

    $locale = app()->getLocale();

    $companyQuery = $cycle->companies()->with(['sectoR', 'stagE']);

    if ($request->filled('sector_id')) {
      $companyQuery->where('sector', $request->sector_id); 
    }

    if ($request->filled('stage_id')) {
      $companyQuery->where('stage', $request->stage_id);
    }

    $companies = $companyQuery->get();

    $sectorDistribution = $companies->groupBy(fn($company) => $company->sectoR?->{"name_$locale"} ?? null)
        ->map(function ($group, $name) use ($companies) {
            $count = $group->count();
            return [
                'sector' => $name,
                'count' => $count,
                'percentage' => round(($count / max($companies->count(), 1)) * 100, 2)
            ];
        })->values();

    return $this->success([
        'filters' => [
            'sectors' => Sector::select('id', "name_$locale as name")->get(),
            'stages' => Stage::select('id', "name_$locale as name")->get()
        ],
        'companies' => $companies->map(fn($company) => [
            'id' => $company->id,
            'name' => $locale === 'ar' ? $company->name_ar : $company->name_en,
        ]),
        'chartData' => [
            'sectorDistribution' => $sectorDistribution,
        ]
    ]);
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{programId}/mentorship",
 *     operationId="getMentorshipReport",
 *     tags={"Programs"},
 *     summary="Get mentorship performance data",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(name="mentor_id", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="company_id", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(
 *         response=200,
 *         description="Mentorship data",
 *         @OA\JsonContent(
 *             @OA\Property(property="kpis", type="object"),
 *             @OA\Property(property="filters", type="object"),
 *             @OA\Property(property="mentorshipByCompany", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="mentorPerformance", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="chartData", type="object")
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Program not found")
 * )
 */
public function mentorship(string $programId, Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('programs.report')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $locale = app()->getLocale();
    $cycle = Cycle::find($programId);
    if(!$cycle) {
        return $this->notFound(__('api.program_not_found'));
    }

    $query = $cycle->mentorshipRequests()->with(['mentor', 'user.company']);

    if ($request->filled('mentor_id')) {
        $query->where('mentor_id', $request->mentor_id);
    }
    if ($request->filled('company_id')) {
        $query->whereHas('user', fn($q) => $q->where('company_id', $request->company_id));
    }
    if ($request->filled('date_from')) {
        $query->whereDate('zoom_date', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('zoom_date', '<=', $request->date_to);
    }

    $requests = $query->get();
    $totalHours = $requests->sum(fn($r) => $r->period ?? 0) / 60;
    $avgRating = round($requests->avg('rating') ?? 0, 1);

    $mentorSessions = $requests->groupBy('mentor_id')->map(fn($group) => [
        'mentorId' => $group->first()?->mentor_id,
        'mentor' => ['name' => $group->first()?->mentor?->name],
        'totalSessions' => $group->count(),
        'totalHours' => round($group->sum('period') / 60, 2),
        'averageRating' => round($group->avg('rating'), 1)
    ])->values();

    $companyStats = $requests->groupBy('user.company_id')->map(fn($group) => [
        'companyId' => $group->first()?->user?->company_id,
        'company' => [
            'name' => $group->first()?->user?->company?->{"name_$locale"},
        ],
        'mentor' => [
            'name' => $group->first()?->mentor?->first_name . ' ' . $group->first()?->mentor?->last_name,
        ],
        'totalSessions' => $group->count(),
        'totalHours' => round($group->sum('period') / 60, 2),
        'mentorRating' => round($group->avg('rating'), 1)
    ])->values();

    return $this->success([
        'kpis' => [
            'totalSessions' => $requests->count(),
            'totalHours' => round($totalHours, 2),
            'averageRating' => $avgRating
        ],
        'filters' => [
            'mentors' => $cycle->mentors->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->first_name . ' ' . $m->last_name,
            ]),
            'companies' => $cycle->companies->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->{"name_$locale"},
            ])
        ],
        'mentorshipByCompany' => $companyStats,
        'mentorPerformance' => $mentorSessions,
        'chartData' => [
            'sessionDistribution' => $mentorSessions->map(fn($m) => [
                'mentorName' => $m['mentor']['name'],
                'sessions' => $m['totalSessions']
            ]),
            'mentorRatings' => $mentorSessions->map(fn($m) => [
                'mentorName' => $m['mentor']['name'],
                'rating' => $m['averageRating']
            ])
        ]
    ]);
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{programId}/workshops",
 *     operationId="getWorkshopStatistics",
 *     tags={"Programs"},
 *     summary="Get workshops, performance, and surveys",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="programId", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Parameter(name="trainer_name", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="workshop_name", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="workshop_type", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(
 *         response=200,
 *         description="Workshops and performance stats",
 *         @OA\JsonContent(
 *             @OA\Property(property="kpis", type="object"),
 *             @OA\Property(property="filters", type="object"),
 *             @OA\Property(property="workshops", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="companyPerformance", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="surveyResponses", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="chartData", type="object")
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Program not found")
 * )
 */

public function workshops(string $programId, Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('programs.report')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $cycle = Cycle::find($programId);
    if(!$cycle) {
        return $this->notFound(__('api.program_not_found'));
    }

    $query = $cycle->trainingSessions();

    if ($request->filled('trainer_name')) {
        $query->where('trainer_name', $request->trainer_name);
    }

    if ($request->filled('workshop_name')) {
        $query->where('training_sessions.title', 'like', '%' . $request->workshop_name . '%');
    }

    if ($request->filled('workshop_type')) {
        $query->where('type', $request->workshop_type);
    }

    $workshops = $query->get();

    $totalMinutes = $workshops->sum('duration');
    $totalHours = round($totalMinutes / 60, 2);
    $onlineCount = $workshops->where('type', 'sessions')->count();
    $offlineCount = $workshops->where('type', 'offline')->count();

    $companies = $cycle->companies;
    $companyIds = $companies->pluck('id')->toArray();
    $locale = app()->getLocale();

    if (!empty($companyIds)) {
    $quizzesResults_q = "
        SELECT 
            qr.id AS qr_result_id,
            qr.user_id AS qr_id,
            qr.quiz_id AS qr_quiz_id,
            qr.score AS qr_result_score,
            qr.status AS qr_result_status,
            c.id as c_company_id,
            c.name_en as c_company_name_en,
            c.name_ar as c_company_name_ar
        FROM quiz_results qr
        LEFT JOIN quizzes q ON qr.quiz_id = q.id
        LEFT JOIN trainings t ON t.id = q.training_id
        LEFT JOIN organizations o ON o.id = q.organization_id
        LEFT JOIN users u ON u.id = qr.user_id
        LEFT JOIN companies c ON c.id = u.company_id
        WHERE c.id IN (" . implode(',', $companyIds) . ")
    ";

    $quizzesResults = collect(DB::select($quizzesResults_q));

    $companyPerformance = $quizzesResults
        ->groupBy('c_company_id')
        ->map(function ($group, $companyId) use ($locale) {
            $companyName = $group->first()->{"c_company_name_{$locale}"} ?? 'N/A';
            $workshopsAttended = $group->count();
            $workshopsCompleted = $group->where('qr_result_status', 1)->count();
            $completionRate = $workshopsAttended > 0
                ? round(($workshopsCompleted / $workshopsAttended) * 100)
                : 0;
            return [
                'id' => (int) $companyId,
                'name' => $companyName,
                'completionRate' => $completionRate,
                'hasPassed' => $completionRate >= 50,
                'workshopsAttended' => $workshopsAttended,
                'workshopsCompleted' => $workshopsCompleted,
            ];
        })
        ->values();
    } else {
     $companyPerformance = collect();
    }
    return $this->success([
        'kpis' => [
            'totalWorkshops' => $workshops->count(),
            'totalHours' => $totalHours,
            'onlineWorkshops' => $onlineCount,
            'offlineWorkshops' => $offlineCount,
        ],
        'filters' => [
            'trainers' => $cycle->trainingSessions()->pluck('trainer_name')->unique()->values()
        ],
        'workshops' => $workshops->map(fn($w) => [
            'id' => $w->id,
            'title' => $w->title,
            'trainerName' => $w->trainer_name,
            'participantsCount' => $w->reports()->count() ?? 0,
            'type' => $w->type,
            'durationHours' => round($w->duration / 60, 2),
            'date' => $w->datetime,
        ]),
        'companyPerformance' => $companyPerformance,
        'surveyResponses' => SurveyResponses::where('cycle_id', $programId)
         ->where('triggered_on', 'training')
         ->WhereNotNull('responses') 
         ->with(['user:id,first_name_en,last_name_en'])
         ->get()
         ->map(function ($response) {
        return [
            'id' => $response->id,
            'user' => [
                'name' => $response->user->first_name_en . ' ' . $response->user->last_name_en,
            ],
            'workshopId' => (int) $response->triggered_at,
            'responses' => json_decode($response->responses, true), 
        ];
    }),
        'chartData' => [
            'workshopTypeDistribution' => [
                ['type' => 'Online', 'count' => $onlineCount],
                ['type' => 'Offline', 'count' => $offlineCount],
            ],
        ]
    ]);
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{programId}/final-evaluation",
 *     operationId="getFinalEvaluation",
 *     tags={"Programs"},
 *     summary="Get final evaluation report for a program",
 *     description="Returns detailed scoring, evaluators' progress, and chart data for a specific program.",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="The ID of the program",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter by evaluation status (e.g., Highest Rated, Good Performance, Needs Improvement)",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="min_score",
 *         in="query",
 *         required=false,
 *         description="Minimum score to filter evaluations",
 *         @OA\Schema(type="number", format="float")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Final evaluation data",
 *         @OA\JsonContent(
 *             @OA\Property(property="evaluations", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="evaluators", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="summary", type="object"),
 *             @OA\Property(property="chartData", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Program not found"
 *     )
 * )
 */

public function finalEvaluation(string $programId, Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('programs.report')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $cycle = Cycle::with('steps')->find($programId);
    if (!$cycle) {
        return $this->notFound(__('api.program_not_found'));
    }

    $statusFilter = $request->get('status');
    $minScoreFilter = $request->get('min_score'); 

    $finalStepIds = $cycle->steps->where('stype', 5)->pluck('id');
    $allCompaniesData = collect();
    $allJudges = collect();

    foreach ($finalStepIds as $stepId) {
        ScreeningEvaluation::firstOrCreate(['step_id' => $stepId]);

        $meta = ScreeningEvaluationResults::metadata($stepId);
        $judges = $meta['judges'];
        $comments = $meta['comments'];
        $companies = $meta['_companies'];

        $allJudges = $allJudges->merge($judges);

        foreach ($companies as $index => $company) {
            $companyId = $company->id;
            $totalScore = 0;
            $publishedEvaluations = 0;
            $evaluationStatus = 'Not Evaluated';
            $teamJudges = collect();

            foreach ($judges as $judge) {
                if (!in_array($companyId, $judge->startups->toArray())) {
                    continue;
                }

                $scoped = collect($judge->companies)->where('id', $companyId)->first();

                if ($scoped && strtolower($scoped->status) === 'published' && isset($scoped->score)) {
                    $comment = $comments->where('user_id', $judge->id)->where('company_id', $companyId)->first()?->value;
                    $judge->comments = $comment;
                    $teamJudges->push($judge);

                    $totalScore += (float)$scoped->score;
                    $publishedEvaluations++;
                    $evaluationStatus = 'published';
                }
            }

            if ($publishedEvaluations > 0) {
                $avgScore = round($totalScore / $publishedEvaluations, 1);

                $companies[$index]->score = $avgScore;
                $companies[$index]->evaluation_status = $evaluationStatus;
                $companies[$index]->company_judges = $teamJudges;
                $allCompaniesData->push($companies[$index]);
            }
        }
    }

    $companyEvaluations = $allCompaniesData
        ->groupBy('id')
        ->map(fn($group) => $group->sortByDesc('score')->first())
        ->values();

    $published = $companyEvaluations->where('evaluation_status', 'published')->values();

    $evaluations = $companyEvaluations->map(function ($company) {
        $score = $company->score;
        $status = match (true) {
            is_null($score) => 'Not Evaluated',
            $score >= 90 => 'Highest Rated',
            $score >= 70 => 'Good Performance',
            default => 'Needs Improvement',
        };

        return [
            'companyId' => $company->id,
            'companyName' => $company->name_en,
            'totalScore' => $score,
            'maxScore' => 100,
            'judgeComments' => collect($company->company_judges)->pluck('comments')->filter()->first(),
            'status' => $status,
            'evaluationDate' => now()->toDateString(),
            'evaluatorName' => collect($company->company_judges)->pluck('name')->first(),
        ];
    });

    if ($statusFilter) {
        $evaluations = $evaluations->where('status', $statusFilter);
    }

    if ($minScoreFilter !== null) {
        $evaluations = $evaluations->filter(function ($item) use ($minScoreFilter) {
            return !is_null($item['totalScore']) && $item['totalScore'] >= $minScoreFilter;
        });
    }

    $evaluations = $evaluations->sortByDesc('totalScore')->values();

    $summary = [
        'highestRated' => $published->where('score', '>=', 90)->map(fn($e) => [
            'companyName' => $e->name_en,
            'score' => $e->score
        ]),
        'needsImprovement' => $published->where('score', '<', 70)->map(fn($e) => [
            'companyName' => $e->name_en,
            'score' => $e->score
        ]),
    ];

    $scoreDistribution = [
        ['scoreRange' => '90-100', 'count' => $published->whereBetween('score', [90, 100])->count()],
        ['scoreRange' => '80-89', 'count' => $published->whereBetween('score', [80, 89.9])->count()],
        ['scoreRange' => '70-79', 'count' => $published->whereBetween('score', [70, 79.9])->count()],
        ['scoreRange' => '60-69', 'count' => $published->whereBetween('score', [60, 69.9])->count()],
        ['scoreRange' => '50-59', 'count' => $published->whereBetween('score', [50, 59.9])->count()],
        ['scoreRange' => '0-49',  'count' => $published->whereBetween('score', [0, 49.9])->count()],
    ];

    $evaluators = $allJudges->unique('id')->map(function ($judge) {
        $published = collect($judge->companies)->where('status', 'Published')->count();
        $draft = collect($judge->companies)->where('status', 'Draft')->count();
        $total = $published + $draft;

        return [
            'evaluatorId' => $judge->id,
            'name' => $judge->name,
            'completedEvaluations' => $published,
            'pendingEvaluations' => $draft,
            'progressPercentage' => $total ? round(($published / $total) * 100, 0) : 0
        ];
    })->values();

    return $this->success([
        'evaluations' => $evaluations->map(function ($item, $index) {
            return array_merge(['rank' => $index + 1], $item);
        }),
        'evaluators' => $evaluators,
        'summary' => $summary,
        'chartData' => [
            'scoreDistribution' => $scoreDistribution
        ]
    ]);
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{programId}/mentors",
 *     summary="Retrieve Program Mentors",
 *     description="Get a paginated list of mentors assigned to a specific program. Only accessible by Admins and Organization Managers.",
 *     operationId="getProgramMentors",
 *     tags={"Programs"},
 *     security={{"bearerAuth": {}}},
 * 
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="The ID of the program",
 *         @OA\Schema(type="integer", example=1234)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Pagination page number",
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         description="Number of items per page",
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         required=false,
 *         description="Search mentors by name, bio, or expertise",
 *         @OA\Schema(type="string", example="Product")
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Mentors retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Mentors retrieved successfully"),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=128),
 *                     @OA\Property(property="name", type="string", example="John Philip"),
 *                     @OA\Property(property="imgUrl", type="string", example="http://localhost:8000/uploads/users/avatar.jpg"),
 *                     @OA\Property(property="title", type="string", example="Senior Mentor"),
 *                     @OA\Property(property="email", type="string", example="mentor@example.com"),
 *                     @OA\Property(property="bio", type="string", example="Experienced startup mentor"),
 *                     @OA\Property(property="specialization", type="string", example="Fintech, SaaS"),
 *                     @OA\Property(property="linkedin", type="string", example="https://linkedin.com/in/mentor"),
 *                     @OA\Property(property="joinedAt", type="string", format="date-time", example="2025-09-11T08:16:48.352018Z"),
 *                     @OA\Property(property="rating", type="number", format="float", example=4.2),
 *                     @OA\Property(
 *                         property="mentorExpertises",
 *                         type="array",
 *                         @OA\Items(type="string", example="Product Development")
 *                     ),
 *                     @OA\Property(property="programCount", type="integer", example=3),
 *                     @OA\Property(property="role", type="string", example="Mentor")
 *                 )
 *             ),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="total", type="integer", example=25),
 *                 @OA\Property(property="page", type="integer", example=1),
 *                 @OA\Property(property="limit", type="integer", example=10),
 *                 @OA\Property(property="totalPages", type="integer", example=3)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User does not have permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Forbidden")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Program not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Program not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request (invalid pagination)",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Invalid pagination parameters")
 *         )
 *     )
 * )
 */

public function mentors(GetProgramMentorsRequest $request, $programId)
{
    if(!$request->user()->ableTo('mentorships.requests_list')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $page = $request->input('page', 1);
    $limit = $request->input('limit', 10);
    $search = $request->input('search');
    $sort = $request->input('sort', 'joinedAt');
    $order = $request->input('order', 'desc');

    $mentors = $this->programService->getProgramMentors($programId, $search, $page, $limit, $sort, $order);

    if ($mentors instanceof \Illuminate\Http\JsonResponse) {
        return $mentors;
    }

    if ($mentors->isEmpty()) {
        return $this->success([], [
            'meta' => [
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => 0
            ]
        ], __('api.noMentorsFound'));
    }

    return $this->success($mentors->items(), [
        'meta' => [
            'total' => $mentors->total(),
            'page' => $mentors->currentPage(),
            'limit' => $mentors->perPage(),
            'totalPages' => $mentors->lastPage()
        ]
    ], __('api.mentorsRetrieved'));
}

/**
 * @OA\Get(
 *     path="/api/v1/programs/{programId}/startups",
 *     summary="List startups in a program",
 *     description="Returns a paginated list of startups associated with a specific program",
 *     tags={"Programs"},
 *     operationId="getProgramStartups",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="ID of the program",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Pagination page number",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         description="Number of items per page",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         required=false,
 *         description="Filter by name or sector",
 *         @OA\Schema(type="string", example="Fintech")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Startups retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Startups retrieved successfully"),
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="logo", type="string", example="https://example.com/logos/techstartup.jpg"),
 *                 @OA\Property(property="name", type="string", example="TechStartup Inc."),
 *                 @OA\Property(property="sector", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Technology")
 *                 ),
 *                 @OA\Property(property="establishmentDate", type="string", format="date-time", example="2023-06-15T00:00:00Z"),
 *                 @OA\Property(property="state", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="California")
 *                 ),
 *                 @OA\Property(property="city", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="San Francisco")
 *                 ),
 *                 @OA\Property(property="country", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="United States")
 *                 ),
 *                 @OA\Property(property="sdgs", type="array", @OA\Items(
 *                     @OA\Property(property="name", type="string", example="Quality Education")
 *                 )),
 *                 @OA\Property(property="SustainableDevelopment", type="array", @OA\Items(
 *                     @OA\Property(property="name", type="string", example="No Poverty")
 *                 )),
 *                 @OA\Property(property="socialLinks", type="object",
 *                     @OA\Property(property="website", type="string", example="https://techstartup.com"),
 *                     @OA\Property(property="linkedin", type="string", example="https://linkedin.com/company/techstartup"),
 *                     @OA\Property(property="x", type="string", example="https://twitter.com/techstartup"),
 *                     @OA\Property(property="facebook", type="string", example="https://facebook.com/techstartup"),
 *                     @OA\Property(property="youtube", type="string", example=null),
 *                     @OA\Property(property="instagram", type="string", example="https://instagram.com/techstartup"),
 *                     @OA\Property(property="tiktok", type="string", example=null)
 *                 ),
 *                 @OA\Property(property="founders", type="array", @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Jane Doe"),
 *                     @OA\Property(property="email", type="string", example="jane@techstartup.com"),
 *                     @OA\Property(property="role", type="string", example="Founder"),
 *                     @OA\Property(property="linkedin", type="string", example="https://linkedin.com/in/janedoe")
 *                 ))
 *             )),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="total", type="integer", example=15),
 *                 @OA\Property(property="page", type="integer", example=1),
 *                 @OA\Property(property="limit", type="integer", example=10),
 *                 @OA\Property(property="totalPages", type="integer", example=2)
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Program not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Program not found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Invalid query parameters",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Invalid pagination parameters")
 *         )
 *     )
 * )
 */

public function startups(GetProgramStartupsRequest $request, $programId)
{
    if (!$request->user()->ableTo('cycles.users_manage')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $page = $request->input('page', 1);
    $limit = $request->input('limit', 10);
    $search = $request->input('search');
    $sort = $request->input('sort', 'id');   
    $order = $request->input('order', 'desc'); 

    $startups = $this->programService->getProgramStartups($programId, $search, $page, $limit, $sort, $order);

    if ($startups instanceof \Illuminate\Http\JsonResponse) {
        return $startups;
    }

    if ($startups->isEmpty()) {
        return $this->success([], [
            'meta' => [
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => 0
            ]
        ], __('api.noStartupsFound'));
    }

    return $this->success(CompanyResource::collection($startups->items()), [
        'meta' => [
            'total' => $startups->total(),
            'page' => $startups->currentPage(),
            'limit' => $startups->perPage(),
            'totalPages' => $startups->lastPage()
        ]
    ], __('api.startupsRetrieved'));
}
}
