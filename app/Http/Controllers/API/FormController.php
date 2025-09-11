<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormListRequest;
use App\Services\FormService;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class FormController extends Controller
{
    use ApiResponder;
    protected FormService $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * @OA\Get(
     *     path="/v1/forms",
     *     summary="Get available forms to use in steps",
     *     tags={"Forms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="stepType",
     *         in="query",
     *         description="Filter forms by step type (e.g. Custom Form, Screening)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Custom Form", "Screening"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available forms retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Startup Application Form"),
     *                 @OA\Property(property="stepTypes", type="array", @OA\Items(type="string", example="Custom Form")),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                 @OA\Property(property="lastModified", type="string", format="date-time", example="2024-02-10T14:30:00Z")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
      public function index(FormListRequest $request)
    {
        if(!auth()->user()->ableToAny(['cycles.list','forms.index'])) {
            return $this->forbidden(__('api.forbidden'));
        }
        $forms = $this->formService->getAvailableForms($request->validated());
        return $this->success($forms);
    }
}
