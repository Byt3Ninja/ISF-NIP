<?php

namespace App\Http\Controllers;

use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currencies = Currency::all();
        return view('admin.currencies.index', compact('currencies'));
    }

     /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.currencies.create');
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCurrencyRequest $request)
    {

        $currency = new Currency();
        $currency->currency_code = $request->currency_code;
        $currency->currency_name = $request->currency_name;
        $currency->currency_name_ar = $request->currency_name_ar;
        $currency->exchange_rate_to_usd = $request->exchange_rate_to_usd;
        $currency->enabled = $request->enabled ? 1 : 0;
        ini_set('memory_limit', '512M');
        $json = File::get(base_path('database/dbFiles/countries_states_cities.json'));
        $data = json_decode($json);
        $iso3Codes =[$request->currency_code];
       
        $counties = array_filter($data, function ($q) use ($iso3Codes) {
            return in_array($q->currency, $iso3Codes);
        });
        foreach ($counties as $value) {
            $currency->currency_symbol = $value->currency_symbol;
            $currency->flag = $value->emoji;
        }
        // Handle file upload
        if ($request->hasFile('flag')) {
            $file = $request->file('flag');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('icons/flags'), $filename);
            $currency->flag = 'icons/flags/' . $filename;
        }
        $currency->save();

        return redirect()->route('currencies.index')->with('msg', 'currencyCreated');
    }

    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency)
    {
        return view('admin.currencies.edit',compact('currency'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        $currency->currency_code = $request->currency_code;
        $currency->currency_name = $request->currency_name;
        $currency->currency_name_ar = $request->currency_name_ar;
        $currency->currency_symbol = $request->currency_symbol;
        $currency->exchange_rate_to_usd = $request->exchange_rate_to_usd;
        $currency->enabled = $request->enabled ? 1 :0;

        // Handle file upload
        if ($request->hasFile('flag')) {
           
            $file = $request->file('flag');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('icons/flags'), $filename);
            $currency->flag = 'icons/flags/' . $filename;
        }

        $currency->save();
        return redirect()->route('currencies.index')->with('msg', 'currencyUpdated');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency)
    {
        $currency->delete();
        return redirect()->route('currencies.index')->with('msg', 'currencyDeleted');
    }

    public function UpdateAllCurrency(Request $request)
    {
        try {
            $response = Http::timeout(10)->get('https://www.floatrates.com/daily/usd.json');
    
            if (!$response->ok()) {
                return response()->json(['error' => __('admin.FailedUpdate')], 500);
            }
    
            $data = $response->json();
    
            $currencies = Currency::where('currency_code', '!=', 'USD')->get();
    
            foreach ($currencies as $currency) {
                $code = strtolower($currency->currency_code);
                if (isset($data[$code])) {
                    $currency->exchange_rate_to_usd = $data[$code]['rate'];
                    $currency->save();
                }
            }
    
            return response()->json(['success' => __('admin.currencyUpdated')]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }

    }
}
