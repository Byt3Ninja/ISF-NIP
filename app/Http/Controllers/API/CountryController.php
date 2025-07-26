<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Http\Resources\CountryResource;

class CountryController extends Controller
{
    use ApiResponder;
    /**
 * @OA\Get(
 *     path="/api/countries",
 *     summary="Get list of countries",
 *     description="Returns a list of all countries based on the current app locale (name_en or name_ar).",
 *     operationId="getCountries",
 *     tags={"Countries"},
 *     @OA\Response(
 *         response=200,
 *         description="Countries retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Egypt")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function index(Request $request)
{
    $countries = DB::table('countries')->get();

    return $this->success(CountryResource::collection($countries));
}
/**
 * @OA\Get(
 *     path="/api/countries/{id}/states",
 *     summary="Get list of states for a country",
 *     description="Returns a list of states for a given country ID based on the current app locale.",
 *     operationId="getStates",
 *     tags={"States"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the country",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="States retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=10),
 *                     @OA\Property(property="title", type="string", example="Cairo")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
public function getStates($id)
{
    $country = DB::table('countries')->where('id', $id)->exists();
    if (!$country) {
        return $this->error(__('api.country_not_found'), 404, null);
    }

    $states = DB::table('states')->where('country_id', $id)->get();
    return $this->success(StateResource::collection($states));
}

}
