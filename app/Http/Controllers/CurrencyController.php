<?php

namespace App\Http\Controllers;

use App\Currency;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use Illuminate\Support\Facades\File;

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
        $currency->exchange_rate_to_usd = $request->exchange_rate_to_usd;
        $currency->enabled = $request->enabled ? 1 : 0;
        $json = File::get(base_path('database/dbFiles/countries+states+cities.json'));
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
        // if ($request->hasFile('flag')) {
        //     $file = $request->file('flag');
        //     $filename = time() . '_' . $file->getClientOriginalName();
        //     $file->move(public_path('icons/flags'), $filename);
        //     $currency->flag = 'icons/flags/' . $filename;
        // }
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
        $url = 'https://www.floatrates.com/daily/usd.json';
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    
    if (!$data) {
        return response()->json(['error' => __('admin.FailedUpdate')]);
    }

    // تحميل ملف الدول مرة واحدة فقط
    $jsonCountries = File::get(base_path('database/dbFiles/countries+states+cities.json'));
    $countriesData = json_decode($jsonCountries, true);
    
    // تحويل الدول إلى مصفوفة مفهرسة بكود العملة
    $countriesIndexed = [];
    foreach ($countriesData as $country) {
        $countriesIndexed[strtoupper($country['currency'])] = $country;
    }

    // تحديث جميع العملات في قاعدة البيانات
    $currencies = Currency::where('currency_code', '!=', 'USD')->get();

    foreach ($currencies as $currency) {
        $currencyCode = strtoupper($currency->currency_code); 

        if (isset($data[strtolower($currencyCode)])) {
            $apiCurrency = $data[strtolower($currencyCode)];
            
            $currency->currency_name = $apiCurrency['name'];
            $currency->exchange_rate_to_usd = $apiCurrency['rate'];

            // جلب العلم والرمز إذا كان موجودًا
            if (isset($countriesIndexed[$currencyCode])) {
                $currency->currency_symbol = $countriesIndexed[$currencyCode]['currency_symbol'] ?? $currency->currency_symbol;
                $currency->flag = $countriesIndexed[$currencyCode]['emoji'] ?? $currency->flag;
            }

            $currency->save();
        }
    }

    return response()->json(['success' => __('admin.currencyUpdated')]);
        // return redirect()->route('currencies.index')->with('msg', 'currencyUpdated');

    }
}
