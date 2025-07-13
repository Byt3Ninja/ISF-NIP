<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/countries",
 *     summary="Get list of countries",
 *     description="Returns a list of all countries based on the current app locale (name_en or name_ar).",
 *     operationId="getCountries",
 *     tags={"Location"},
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
    $locale = app()->getLocale(); 
    $nameColumn = $locale === 'ar' ? 'name_ar' : 'name_en';

    $countries = DB::table('countries')->select('id', "$nameColumn as title")->get();

    return response()->json([
        'success' => true,
        'data' => $countries
    ]);
}
/**
 * @OA\Get(
 *     path="/api/countries/{id}/states",
 *     summary="Get list of states for a country",
 *     description="Returns a list of states for a given country ID based on the current app locale.",
 *     operationId="getStates",
 *     tags={"Location"},
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
    $locale = app()->getLocale(); 
    $nameColumn = $locale === 'ar' ? 'name_ar' : 'name_en';

    $states = DB::table('states')->where('country_id', $id)
                ->select('id', "$nameColumn as title")
                ->get();

    return response()->json([
        'success' => true,
        'data' => $states
    ]);
}

}
