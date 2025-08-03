<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Cycle;
use App\Messenger;
use Carbon\Carbon;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\ProgramResource;
use App\Mail\ProgramCreatedNotification;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;

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

            if ($request->hasFile('programGuidelineFile')) {
                $originalName = $request->file('programGuidelineFile')->getClientOriginalName();
                $cycle->program_guideline_file = $request->file('programGuidelineFile')->storeAs('programs', $originalName, 'public_uploads');
            }

            if ($request->hasFile('financialPolicyPdf')) {
                $originalName = $request->file('financialPolicyPdf')->getClientOriginalName();
                $cycle->financial_guideline_file = $request->file('financialPolicyPdf')->storeAs('programs', $originalName, 'public_uploads');
            }

            if ($request->hasFile('legalPolicyPdf')) {
                $originalName = $request->file('legalPolicyPdf')->getClientOriginalName();
                $cycle->legal_guideline_file = $request->file('legalPolicyPdf')->storeAs('programs', $originalName, 'public_uploads');
            }

            if ($request->hasFile('delegationPdf')) {
                $originalName = $request->file('delegationPdf')->getClientOriginalName();
                $cycle->delegation_file = $request->file('delegationPdf')->storeAs('programs', $originalName, 'public_uploads');
            }

            if ($request->hasFile('imgUrl')) {
                 $path = $request->file('imgUrl')->store('programs', 'public_uploads');
                 $cycle->img_url = $path;
            }
            if (auth()->user()->hasRole('OrganizationManager')) {

                    $admin = User::role('Admin')->orderBy('id')->first();

                    if ($admin) {

                        Mail::to($admin->email)->queue(new ProgramCreatedNotification($cycle, $admin));

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

    $cycle = Cycle::findOrFail($id);

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

    if (isset($validated['imgUrl'])) {
        $cycle->img_url = $validated['imgUrl']->store('programs', 'public_uploads');
    }

    if (isset($validated['programGuidelineFile'])) {
        $fileName = uniqid() . '_' . $validated['programGuidelineFile']->getClientOriginalName();
        $cycle->program_guideline_file = $validated['programGuidelineFile']->storeAs('programs', $fileName, 'public_uploads');
    }

    if (isset($validated['financialPolicyPdf'])) {
        $fileName = uniqid() . '_' . $validated['financialPolicyPdf']->getClientOriginalName();
        $cycle->financial_guideline_file = $validated['financialPolicyPdf']->storeAs('programs', $fileName, 'public_uploads');
    }

    if (isset($validated['legalPolicyPdf'])) {
        $fileName = uniqid() . '_' . $validated['legalPolicyPdf']->getClientOriginalName();
        $cycle->legal_guideline_file = $validated['legalPolicyPdf']->storeAs('programs', $fileName, 'public_uploads');
    }

    if (isset($validated['delegationPdf'])) {
        $fileName = uniqid() . '_' . $validated['delegationPdf']->getClientOriginalName();
        $cycle->delegation_file = $validated['delegationPdf']->storeAs('programs', $fileName, 'public_uploads');
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

      if ($user->hasRole('organizationManager')) {
          $query->where('organization_id', $user->organization_id);
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

        $programs = $query->latest()->paginate($request->get('limit', 10));
        // Check if there are no results
        if ($programs->isEmpty()) {
          return $this->notFound(__('api.programs_not_found'));
        }

        return $this->success(ProgramResource::collection($programs),[
            'total' => $programs->total(),
            'page' => $programs->currentPage(),
            'limit' => $programs->perPage(),
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

}
