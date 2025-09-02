<?php

namespace App\Http\Controllers\API;

use App\Currency;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\CurrencyResource;
use App\Repositories\CurrencyRepository;
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
    protected $service;
    protected $repo;

    public function __construct(CurrencyService $service, CurrencyRepository $repo)
    {
        $this->service = $service;
        $this->repo = $repo;
    }
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
        return $this->success(CurrencyResource::collection($this->service->getEnabledCurrencies()));
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
        $this->authorize('viewAny', Currency::class);
        $limit = $request->get('limit', 10);
        $enabled = $request->has('enabled') ? $request->boolean('enabled') : null;

        $currencies = $this->service->paginateCurrencies($limit, $enabled);

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
        $this->authorize('create', Currency::class);
        $currency = $this->service->store($request->validated(), $request->file('flag'));
        return $this->created(new CurrencyResource($currency), __('api.Currency_created'));
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
        $currency = $this->repo->find($id);
        if (!$currency) {
            return $this->notFound(__('api.currency_not_found'));
        }
        $this->authorize('update', $currency);
        $updated = $this->service->update($currency, $request->validated(), $request->file('flag'));

        return $this->success(new CurrencyResource($updated), [], __('api.Currency_updated'));
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
        $currency = $this->repo->find($id);
        if (!$currency) {
            return $this->notFound(__('api.currency_not_found'));
        }

        $this->authorize('delete', $currency);
        $this->service->delete($currency);
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
    $this->authorize('updateRates', Currency::class);

    $success = $this->service->updateExchangeRates();

    if (!$success) {
        return $this->serverError(__('admin.FailedUpdate'));
    }

    return $this->success(null, [], __('api.currencyRatesUpdated'));
}
}
