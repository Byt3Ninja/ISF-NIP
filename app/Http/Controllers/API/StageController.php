<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StageResource;

class StageController extends Controller
{
    use ApiResponder;
        /**
     * @OA\Get(
     *     path="/api/stages",
     *     summary="Get all stages",
     *     tags={"Stages"},
     *     @OA\Response(
     *         response=200,
     *         description="List of stages",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
     public function index()
    {
        $stages = DB::table('stages')->get();
        return $this->success(StageResource::collection($stages));
    }
}
