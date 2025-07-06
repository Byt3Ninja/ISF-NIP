<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ExpertiseController extends Controller
{
  /**
 * @OA\Get(
 *     path="/api/expertises",
 *     summary="Get All Expertises",
 *     description="Returns a flat list of expertises with localized titles based on the `lang` header.",
 *     tags={"Expertises"},
 *     @OA\Parameter(
 *         name="lang",
 *         in="header",
 *         required=false,
 *         description="Language preference. Allowed values: en, ar. Defaults to en.",
 *         @OA\Schema(type="string", enum={"en", "ar"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response with localized expertises",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Product Development")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function index()
    {
      $lang = app()->getLocale();
      $column = $lang === 'ar' ? 'title_ar' : 'title';
      $expertises = DB::table('expertises')
        ->select('id', "$column as title")
        ->get();

     return response()->json([
        'status' => true,
        'data' => $expertises,
     ]);
     
    }

}
