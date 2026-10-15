<?php

namespace App\Http\Controllers\API;

use App\Step;
use App\Cycle;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\StepService;
use App\Http\Controllers\Controller;
use App\Http\Resources\StepResource;
use App\Http\Requests\StoreStepRequest;
use App\Http\Requests\UpdateStepRequest;
use App\Http\Requests\ReorderStepsRequest;
use App\Http\Requests\ChangeStepOrderRequest;

/**
 * @OA\Schema(
 *     schema="Step",
 *     type="object",
 *     title="Step",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Screening Phase"),
 *     @OA\Property(property="description", type="string", example="Evaluate startups using a custom form"),
 *     @OA\Property(property="startDate", type="string", format="date", example="2025-08-01"),
 *     @OA\Property(property="endDate", type="string", format="date", example="2025-08-15"),
 *     @OA\Property(
 *         property="program",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=12),
 *         @OA\Property(property="title", type="string", example="Cycle 1 - Pre-Incubation"),
 *         @OA\Property(property="description", type="string", example="First round of incubation")
 *     ),
 *     @OA\Property(property="stepArrange", type="integer", example=2),
 *     @OA\Property(property="stepTypeId", type="integer", example=2),
 *     @OA\Property(property="stepType", type="string", example="Screening"),
 *     @OA\Property(property="formId", type="integer", nullable=true, example=5),
 *     @OA\Property(property="formName", type="string", nullable=true, example="Evaluation Form 2025")
 * )
 */
class StepController extends Controller
{
    use ApiResponder;
    protected StepService $stepService;

    public function __construct(StepService $stepService)
    {
        $this->stepService = $stepService;
    }

  /**
 * @OA\Get(
 *     path="/api/steps",
 *     summary="Get Steps by Cycle ID",
 *     tags={"Steps"},
 *     @OA\Parameter(
 *         name="cycle_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of steps for the specified cycle",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Request successful"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Step")
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="total", type="integer", example=25),
 *                 @OA\Property(property="page", type="integer", example=1),
 *                 @OA\Property(property="limit", type="integer", example=10)
 *             ),
 *             @OA\Property(
 *                 property="links",
 *                 type="object",
 *                 @OA\Property(property="first", type="string", example="http://example.com/api/steps?page=1"),
 *                 @OA\Property(property="last", type="string", example="http://example.com/api/steps?page=3"),
 *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
 *                 @OA\Property(property="next", type="string", nullable=true, example="http://example.com/api/steps?page=2")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Cycle ID not provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Steps not found for the given cycle"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     )
 * )
 */
    public function index(Request $request)
    {
      $user = auth()->user();
      if (!$user->ableTo('cycles.list')) {
        return $this->forbidden(__('api.forbidden'));
      }
    
      if (!$request->has('cycle_id')) {
        return $this->notFound(__('api.stepsNotFound'));
       }

      // cycle_id arrives from the query string, so the tenant must be verified
      // before listing. Every other action in this controller already does this;
      // index() was the remaining cross-tenant read.
      $cycle = Cycle::find($request->cycle_id);
      if (empty($cycle)) {
        return $this->notFound(__('api.stepsNotFound'));
      }

      if (!$user->hasRole('admin') && !$user->ownsOrganization($cycle->organization_id)) {
        return $this->forbidden(__('api.forbidden'));
      }

      $steps = Step::with(['cycle', 'form'])->where('cycle_id', $request->cycle_id)->orderBy('arr')->paginate(10);

      return $this->success(StepResource::collection($steps->items()),
       [
        'meta' => [
            'total' => $steps->total(),
            'page' => $steps->currentPage(),
            'limit' => $steps->perPage(),
        ],
        'links' => [
            'first' => $steps->url(1),
            'last' => $steps->url($steps->lastPage()),
            'prev' => $steps->previousPageUrl(),
            'next' => $steps->nextPageUrl(),
        ],
       ]
      );

    }

   /**
 * @OA\Post(
 *     path="/v1/programs/{programId}/steps",
 *     summary="Create a new step for a program",
 *     tags={"Steps"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *         @OA\Schema(type="integer", example=38)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string", example="Custom Form Step"),
 *             @OA\Property(property="description", type="string", example="This is a custom step"),
 *             @OA\Property(property="stepType", type="string", example="Custom Form"),
 *             @OA\Property(property="stepTypeId", type="integer", example=1),
 *             @OA\Property(property="startDate", type="string", format="date", example="2025-10-01"),
 *             @OA\Property(property="endDate", type="string", format="date", example="2025-10-15"),
 *             @OA\Property(property="stepArrange", type="integer", example=1),
 *             @OA\Property(property="count_team_number", type="integer", example=5),
 *             @OA\Property(property="formId", type="integer", example=12),
 *             @OA\Property(property="formName", type="string", example="My Evaluation Form"),
 *             @OA\Property(property="screeningType", type="string", example="onetoall", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Step created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Step created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=123),
 *                 @OA\Property(property="title", type="string", example="Custom Form Step"),
 *                 @OA\Property(property="stepArrange", type="integer", example=1),
 *                 @OA\Property(property="createdAt", type="string", example="2025-09-01T10:00:00Z"),
 *                 @OA\Property(property="updatedAt", type="string", example="2025-09-01T10:00:00Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Program not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

    public function createStep(StoreStepRequest $request, $programId)
    {
      if(!auth()->user()->ableTo('cycles.modify')) {
       return $this->forbidden(__('api.forbidden'));
      }
      $program = Cycle::find($programId);
      if (empty($program)) {
          return $this->notFound(__('api.program_not_found'));
      }

      $authUser = auth()->user();
      if (!$authUser->hasRole('admin') && !$authUser->ownsOrganization($program->organization_id)) {
          return $this->forbidden(__('api.forbidden'));
      }

      $step = $this->stepService->createStep(array_merge($request->validated(),['programId' => $programId]));
      return $this->created(new StepResource($step),__('api.step_created'));
    }

   /**
 * @OA\Put(
 *     path="/v1/programs/{programId}/steps/{stepId}",
 *     summary="Update a step in a program",
 *     tags={"Steps"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Program ID"
 *     ),
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Step ID"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string", example="Updated Step Title"),
 *             @OA\Property(property="description", type="string", example="Updated description"),
 *             @OA\Property(property="stepType", type="string", example="Custom Form"),
 *             @OA\Property(property="stepTypeId", type="integer", example=1),
 *             @OA\Property(property="startDate", type="string", format="date", example="2025-10-01"),
 *             @OA\Property(property="endDate", type="string", format="date", example="2025-10-10"),
 *             @OA\Property(property="stepArrange", type="integer", example=2),
 *             @OA\Property(property="count_team_number", type="integer", example=5),
 *             @OA\Property(property="formId", type="integer", example=12),
 *             @OA\Property(property="formName", type="string", example="Updated Form"),
 *             @OA\Property(property="screeningType", type="string", example="onetoall", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Step updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Step updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=123),
 *                 @OA\Property(property="title", type="string", example="Updated Step Title")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Step or Program not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

   public function updateStep(UpdateStepRequest $request, $programId, $stepId)
 {
    if(!auth()->user()->ableTo('cycles.modify')) {
      return $this->forbidden(__('api.forbidden'));
    }
    $program = Cycle::find($programId);
    if (empty($program)) {
        return $this->notFound(__('api.program_not_found'));
    }

    $authUser = auth()->user();
    if (!$authUser->hasRole('admin') && !$authUser->ownsOrganization($program->organization_id)) {
        return $this->forbidden(__('api.forbidden'));
    }

    $step = $this->stepService->updateStep(array_merge($request->validated(),['programId' => $programId]), $stepId);

    return $this->success(new StepResource($step),[], __('api.step_updated'));
 }

 /**
 * @OA\Delete(
 *     path="/v1/programs/{programId}/steps/{stepId}",
 *     summary="Delete a step from the program",
 *     tags={"Steps"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Step deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Step deleted successfully"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Step or Program not found"
 *     )
 * )
 */

  public function deleteStep($programId, $stepId)
 {
    if(!auth()->user()->ableTo('cycles.modify')) {
      return $this->forbidden(__('api.forbidden'));
    }
    $program = Cycle::find($programId);
    if (empty($program)) {
        return $this->notFound(__('api.program_not_found'));
    }

    $authUser = auth()->user();
    if (!$authUser->hasRole('admin') && !$authUser->ownsOrganization($program->organization_id)) {
        return $this->forbidden(__('api.forbidden'));
    }

    $this->stepService->deleteStep($stepId, $programId);
    return $this->success(null, [], __('api.step_deleted'));
 }

 /**
 * @OA\Patch(
 *     path="/v1/programs/{programId}/steps/reorder",
 *     summary="Reorder multiple steps within a program",
 *     tags={"Steps"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Program ID"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="steps",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=123),
 *                     @OA\Property(property="stepArrange", type="integer", example=1)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Steps reordered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Steps reordered successfully"),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=123),
 *                     @OA\Property(property="stepArrange", type="integer", example=1)
 *                 )
 *             )
 *         )
 *     )
 * )
 */
  public function reorderSteps(ReorderStepsRequest $request, $programId)
  {
    if(!auth()->user()->ableTo('cycles.list')) {
     return $this->forbidden(__('api.forbidden'));
    }
    $program = Cycle::find($programId);
    if (empty($program)) {
        return $this->notFound(__('api.program_not_found'));
    }

    $authUser = auth()->user();
    if (!$authUser->hasRole('admin') && !$authUser->ownsOrganization($program->organization_id)) {
        return $this->forbidden(__('api.forbidden'));
    }

    $steps = $this->stepService->reorderSteps($programId, $request->validated()['steps']);
    return $this->success($steps, [], __('api.steps_reordered_successfully'));
  }

/**
 * @OA\Patch(
 *     path="/v1/programs/{programId}/steps/{stepId}/change-order",
 *     summary="Change the order of a single step",
 *     tags={"Steps"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Program ID"
 *     ),
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Step ID to move"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="newPosition", type="integer", example=2),
 *             @OA\Property(property="direction", type="string", enum={"before", "after"}, example="after"),
 *             @OA\Property(property="targetStepId", type="integer", example=124)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Step order changed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Step order changed successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=123),
 *                 @OA\Property(property="stepArrange", type="integer", example=3),
 *                 @OA\Property(
 *                     property="affectedSteps",
 *                     type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="id", type="integer", example=124),
 *                         @OA\Property(property="stepArrange", type="integer", example=1)
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */

  public function changeStepOrder(ChangeStepOrderRequest $request, $programId, $stepId)
  {
  if(!auth()->user()->ableTo('cycles.list')) {
    return $this->forbidden(__('api.forbidden'));
  }
  $program = Cycle::find($programId);
  if (empty($program)) {
      return $this->notFound(__('api.program_not_found'));
  }

  $authUser = auth()->user();
  if (!$authUser->hasRole('admin') && !$authUser->ownsOrganization($program->organization_id)) {
      return $this->forbidden(__('api.forbidden'));
  }

  $data = $request->validated();
  $result = $this->stepService->changeStepOrder($programId, $stepId, $data);

  return $this->success($result, [], __('api.step_order_changed_successfully'));
  }

  /**
 * @OA\Get(
 *     path="/v1/step-types",
 *     summary="Get available step types",
 *     tags={"Steps"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of step types",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Custom Form"),
 *                 @OA\Property(property="description", type="string", example="Step with custom form"),
 *                 @OA\Property(property="requiresForm", type="boolean", example=true),
 *                 @OA\Property(property="allowsScreeningType", type="boolean", example=false)
 *             ))
 *         )
 *     )
 * )
 */
public function getStepTypes()
{
  if(!auth()->user()->ableTo('cycles.list')) {
    return $this->forbidden(__('api.forbidden'));
  }
  $types = $this->stepService->getStepTypes();
  return $this->success($types->values());
}

}
