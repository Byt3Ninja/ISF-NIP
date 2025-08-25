<?php

namespace App\Http\Controllers\API;

use App\Currency;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\CurrencyResource;
use App\Http\Requests\StoreCurrencyApiRequest;
use App\Http\Requests\UpdateCurrencyApiRequest;
/**
 * @OA\Schema(
 *     schema="Currency",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="currencyCode", type="string", example="USD"),
 *     @OA\Property(property="currencyName", type="string", example="US Dollar"),
 *     @OA\Property(property="currencySymbol", type="string", example="$"),
 *     @OA\Property(property="exchangeRateToUsd", type="number", format="float", example=1.0000),
 *     @OA\Property(property="enabled", type="boolean", example=true),
 *     @OA\Property(property="flag", type="string", example="http://yourdomain.com/icons/flags/us.png")
 * )
 */
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
    /**
 * @OA\Get(
 *     path="/api/currencies-all",
 *     summary="Get all currencies (paginated)",
 *     description="Returns paginated currencies with optional enabled filter.",
 *     operationId="getCurrenciesPaginated",
 *     tags={"Currencies"},
 *     @OA\Parameter(
 *         name="enabled",
 *         in="query",
 *         description="Filter by enabled status (1 = enabled, 0 = disabled)",
 *         required=false,
 *         @OA\Schema(type="boolean")
 *     ),
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         description="Number of items per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Paginated list of currencies",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Currency")),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="total", type="integer", example=100),
 *                 @OA\Property(property="page", type="integer", example=1),
 *                 @OA\Property(property="limit", type="integer", example=10)
 *             ),
 *             @OA\Property(property="links", type="object",
 *                 @OA\Property(property="first", type="string", example="http://localhost/api/currencies?page=1"),
 *                 @OA\Property(property="last", type="string"),
 *                 @OA\Property(property="prev", type="string", nullable=true),
 *                 @OA\Property(property="next", type="string", nullable=true)
 *             )
 *         )
 *     )
 * )
 */
    public function currencies(Request $request)
    {
        $user = auth()->user();
        if (!$user->ableTo('currencies.index')) {
            return $this->forbidden(__('api.forbidden'));
        }
        $query = DB::table('currencies');

        if ($request->has('enabled')) {
         $enabled = $request->boolean('enabled');
            $query->where('enabled', $enabled);
         }

         $limit = $request->get('limit', 10);
         $currencies = $query->orderByDesc('last_updated')->paginate($limit);

        if ($currencies->isEmpty()) {
         return $this->notFound(__('api.currencies_not_found'));
        }

     return $this->success(
        CurrencyResource::collection($currencies->items()),
        [
            'meta' => [
                'total' => $currencies->total(),
                'page' => $currencies->currentPage(),
                'limit' => $currencies->perPage(),
            ],
            'links' => [
                'first' => $currencies->url(1),
                'last' => $currencies->url($currencies->lastPage()),
                'prev' => $currencies->previousPageUrl(),
                'next' => $currencies->nextPageUrl(),
            ],
        ]
     );
    }
    /**
 * @OA\Post(
 *     path="/api/currencies",
 *     summary="Create new currency",
 *     description="Stores a new currency with code, name, symbol, rate, and optional flag.",
 *     operationId="storeCurrency",
 *     tags={"Currencies"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"currencyCode", "currencyName", "currencySymbol", "exchangeRateToUsd"},
 *                 @OA\Property(property="currencyCode", type="string", maxLength=3, example="USD"),
 *                 @OA\Property(property="currencyName", type="string", example="US Dollar"),
 *                 @OA\Property(property="currencySymbol", type="string", example="$"),
 *                 @OA\Property(property="exchangeRateToUsd", type="number", format="float", example=1.0),
 *                 @OA\Property(property="enabled", type="boolean", example=true),
 *                 @OA\Property(property="flag", type="file", description="Currency flag image (optional)")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Currency created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Currency created successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/Currency")
 *         )
 *     )
 * )
 */

     public function store(StoreCurrencyApiRequest $request)
    {
        $user = auth()->user();
        if (!$user->ableToAny(['currencies.create', 'currencies.store'])) {
          return $this->forbidden(__('api.forbidden'));
        }
        $currency = new Currency();
        $currency->currency_code = $request->currencyCode;
        $currency->currency_name = $request->currencyName;
        $currency->currency_symbol = $request->currencySymbol;
        $currency->exchange_rate_to_usd = $request->exchangeRateToUsd;
        $currency->enabled = $request->enabled ?? 0;

        if ($request->hasFile('flag')) {
            $file = $request->file('flag');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('icons/flags'), $filename);
            $currency->flag = 'icons/flags/' . $filename;
        }

        $currency->save();

        return $this->created(new CurrencyResource($currency),__('api.Currency_created'));
    }

    /**
 * @OA\Post(
 *     path="/api/currencies/{id}",
 *     summary="Update currency",
 *     description="Updates an existing currency by ID.",
 *     operationId="updateCurrency",
 *     tags={"Currencies"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the currency to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="currencyCode", type="string", example="USD"),
 *                 @OA\Property(property="currencyName", type="string", example="US Dollar"),
 *                 @OA\Property(property="currencySymbol", type="string", example="$"),
 *                 @OA\Property(property="exchangeRateToUsd", type="number", format="float", example=1.0),
 *                 @OA\Property(property="enabled", type="boolean", example=true),
 *                 @OA\Property(property="flag", type="file", description="Currency flag image (optional)")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Currency updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Currency updated successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/Currency")
 *         )
 *     )
 * )
 */
    public function update(UpdateCurrencyApiRequest $request, $id)
    {
        $user = auth()->user();
        if (!$user->ableToAny(['currencies.edit', 'currencies.update'])) {
          return $this->forbidden(__('api.forbidden'));
        }
       $currency = Currency::find($id);

       if (!$currency) {
        return $this->notFound(__('api.currency_not_found'));
       }

        $currency->currency_code = $request->has('currencyCode') ? $request->currencyCode : $currency->currency_code;
        $currency->currency_name = $request->has('currencyName') ? $request->currencyName : $currency->currency_name;
        $currency->currency_symbol = $request->has('currencySymbol') ? $request->currencySymbol : $currency->currency_symbol;
        $currency->exchange_rate_to_usd = $request->has('exchangeRateToUsd') ? $request->exchangeRateToUsd : $currency->exchange_rate_to_usd;
        $currency->enabled = $request->has('enabled') ? $request->enabled : $currency->enabled;

        // Handle file upload if flag is provided
        if ($request->hasFile('flag')) {
            $file = $request->file('flag');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('icons/flags'), $filename);
            $currency->flag = 'icons/flags/' . $filename;
        }

        $currency->last_updated = now();
        $currency->save();

      return $this->success(new CurrencyResource($currency), [], __('api.Currency_updated'));
    }

    /**
 * @OA\Delete(
 *     path="/api/currencies/{id}",
 *     summary="Delete currency",
 *     description="Deletes a currency by ID.",
 *     operationId="deleteCurrency",
 *     tags={"Currencies"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the currency to delete",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Currency deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Currency deleted successfully"),
 *             @OA\Property(property="data", type="null")
 *         )
 *     )
 * )
 */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->ableTo('currencies.destroy')) {
            return $this->forbidden(__('api.forbidden'));
        }
        $currency = Currency::find($id);
        if (!$currency) {
            return $this->notFound(__('api.currency_not_found'));
        }
        $currency->delete();
        return $this->success(null, [], __('api.currency_deleted'));
    }
/**
 * @OA\Patch(
 *     path="/api/currencies/exchange-rates",
 *     summary="Update all exchange rates",
 *     description="Fetches latest exchange rates (USD base) and updates all currencies except USD.",
 *     operationId="updateExchangeRates",
 *     tags={"Currencies"},
 *     @OA\Response(
 *         response=200,
 *         description="Rates updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Exchange rates updated successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update rates",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Failed to update currency exchange rates")
 *         )
 *     )
 * )
 */
    public function updateAllCurrency()
{
    $user = auth()->user();
    if (!$user->ableTo('currencies.rates_update_all')) {
        return $this->forbidden(__('api.forbidden'));
    }

    try {
        $response = Http::timeout(10)->get('https://www.floatrates.com/daily/usd.json');

        if (!$response->ok()) {
            return $this->error(__('admin.FailedUpdate'), 500);
        }

        $data = $response->json();
        $currencies = Currency::where('currency_code', '!=', 'USD')->get();

        foreach ($currencies as $currency) {
            $code = strtolower($currency->currency_code);
            if (isset($data[$code])) {
                $currency->exchange_rate_to_usd = $data[$code]['rate'];
                $currency->last_updated = now();
                $currency->save();
            }
        }

        return $this->success(null, [], __('api.currencyRatesUpdated'));
    } catch (\Exception $e) {
        return $this->serverError(__('admin.FailedUpdate') . ': ' . $e->getMessage());
    }
}
}
