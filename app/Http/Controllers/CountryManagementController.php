<?php

namespace App\Http\Controllers;

use App\Country;
use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;

class CountryManagementController extends Controller
{
    public function index()
    {
        $countries = Country::withCount('states')->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('admin.countries.create');
    }

    public function store(StoreCountryRequest $request)
    {
        Country::create($request->validated());
        return redirect()->route('country-management.index')->with('msg', 'StoredSuccessfully');
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    public function update(UpdateCountryRequest $request, Country $country)
    {
        $country->update($request->validated());
        return redirect()->route('country-management.index')->with('msg', 'UpdatedSuccessfully');
    }

    public function destroy(Country $country)
    {
        $country->delete();
        return redirect()->route('country-management.index')->with('msg', 'DeletedSuccessfully');
    }
}
