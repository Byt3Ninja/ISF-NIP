<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;

class CurrencyController extends Controller
{
     use ApiResponder;
     /**
 * @OA\Get(
 *     path="/api/currencies",
 *     summary="Get enabled currencies",
 *     description="Returns a list of all enabled currencies with code, name, symbol, rate, and flag.",
 *     operationId="getCurrencies",
 *     tags={"Currencies"},
 *     @OA\Response(
 *         response=200,
 *         description="Currencies retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="currencyCode", type="string", example="USD"),
 *                     @OA\Property(property="currencyName", type="string", example="US Dollar"),
 *                 )
 *             )
 *         )
 *     )
 * )
 */

    public function index()
    {
        $currencies = DB::table('currencies')->where('enabled', 1)->get();

        return $this->success(CurrencyResource::collection($currencies));
    }
}
