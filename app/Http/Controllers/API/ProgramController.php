<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Cycle;
use App\Messenger;
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
 *                 required={"organization", "type", "title", "start", "end"},
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
 *                 @OA\Property(property="programGuidelineFile", type="file"),
 *                 @OA\Property(property="financialPolicyPdf", type="file"),
 *                 @OA\Property(property="legalPolicyPdf", type="file"),
 *                 @OA\Property(property="delegationPdf", type="file"),
 *                 @OA\Property(property="imgUrl", type="file"),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Program created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/ProgramResource")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
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
            $cycle->organization_id = $validated['organization'];
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
 *                 @OA\Property(property="organization", type="integer", example=1),
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
 *                 @OA\Property(property="programGuidelineFile", type="file"),
 *                 @OA\Property(property="financialPolicyPdf", type="file"),
 *                 @OA\Property(property="legalPolicyPdf", type="file"),
 *                 @OA\Property(property="delegationPdf", type="file"),
 *                 @OA\Property(property="imgUrl", type="file"),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Program updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/ProgramResource")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Program not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
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

     if (isset($validated['organization'])) {
        $cycle->organization_id = $validated['organization'];
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

}
