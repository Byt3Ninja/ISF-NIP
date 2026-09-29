<?php

namespace App\Http\Controllers;

use App\Country;
use App\State;
use App\Http\Requests\StoreStateRequest;
use App\Http\Requests\UpdateStateRequest;

class StateManagementController extends Controller
{
    public function index(Country $country)
    {
        $states = $country->states()->withCount('cities')->get();
        return view('admin.states.index', compact('country', 'states'));
    }

    public function create(Country $country)
    {
        return view('admin.states.create', compact('country'));
    }

    public function store(StoreStateRequest $request, Country $country)
    {
        $country->states()->create($request->validated());
        return redirect()->route('state-management.index', $country->id)->with('msg', 'StoredSuccessfully');
    }

    public function edit(State $state)
    {
        $state->load('country');
        return view('admin.states.edit', compact('state'));
    }

    public function update(UpdateStateRequest $request, State $state)
    {
        $state->update($request->validated());
        return redirect()->route('state-management.index', $state->country_id)->with('msg', 'UpdatedSuccessfully');
    }

    public function destroy(State $state)
    {
        $countryId = $state->country_id;
        $state->delete();
        return redirect()->route('state-management.index', $countryId)->with('msg', 'DeletedSuccessfully');
    }
}
