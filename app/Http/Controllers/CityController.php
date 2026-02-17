<?php

namespace App\Http\Controllers;

use App\City;
use App\State;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;

class CityController extends Controller
{
    /**
     * API: Return cities as JSON for a given state_id (used by cascade dropdowns).
     */
    public function index()
    {
        $cities = City::where('state_id', request()->input('state_id', 0))
            ->pluck(lang('name') . " as name", 'id');
        return response()->json($cities);
    }

    /**
     * API: Return states as JSON for a given country_id (used by cascade dropdowns).
     */
    public function states()
    {
        $states = State::where('country_id', request()->input('country_id', 0))
            ->pluck(lang('name') . " as name", 'id');
        return response()->json($states);
    }

    // ─── Admin CRUD ────────────────────────────────────────────

    public function adminIndex(State $state)
    {
        $state->load('country');
        $cities = $state->cities;
        return view('admin.cities.index', compact('state', 'cities'));
    }

    public function adminCreate(State $state)
    {
        $state->load('country');
        return view('admin.cities.create', compact('state'));
    }

    public function adminStore(StoreCityRequest $request, State $state)
    {
        $state->cities()->create(array_merge(
            $request->validated(),
            ['country_id' => $state->country_id]
        ));
        return redirect()->route('city-management.index', $state->id)->with('msg', 'StoredSuccessfully');
    }

    public function adminEdit(City $city)
    {
        $city->load('state.country');
        return view('admin.cities.edit', compact('city'));
    }

    public function adminUpdate(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());
        return redirect()->route('city-management.index', $city->state_id)->with('msg', 'UpdatedSuccessfully');
    }

    public function adminDestroy(City $city)
    {
        $stateId = $city->state_id;
        $city->delete();
        return redirect()->route('city-management.index', $stateId)->with('msg', 'DeletedSuccessfully');
    }
}
