<?php

namespace App\Http\Controllers\API;

use App\Step;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StepResource;

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
}
