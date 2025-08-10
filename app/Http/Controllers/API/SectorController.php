<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Http\Resources\SectorResource;

class SectorController extends Controller
{
    use ApiResponder;

      /**
     * @OA\Get(
     *     path="/api/sectors",
     *     summary="Get all sectors",
     *     tags={"Sectors"},
     *     @OA\Response(
     *         response=200,
     *         description="List of sectors",
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
        $sectors = DB::table('sectors')->get();
        return $this->success(SectorResource::collection($sectors));
    }

       /**
     * @OA\Get(
     *     path="/api/states",
     *     summary="Get all states",
     *     tags={"States"},
     *     @OA\Response(
     *         response=200,
     *         description="List of states",
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
     public function getStates()
    {
        $states = DB::table('states')->get();
        return $this->success(StateResource::collection($states));
    }
}
