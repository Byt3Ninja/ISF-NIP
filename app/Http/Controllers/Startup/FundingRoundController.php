<?php

namespace App\Http\Controllers\Startup;

use App\Events\WebMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Startup\FundingStoreRequest;
use Illuminate\Http\Request;

class FundingRoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FundingStoreRequest $request)
    {
        $round = auth()->user()->company->fundingRounds()->create($request->validated());
        if($round) {
            return redirect()->route('company.profile_view_mine')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
        }
        else {
            return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // H3: only the user's own startup's rounds (as update() already did).
        $round = auth()->user()->company?->fundingRounds()->findOrFail($id) ?? abort(404);
        return response()->json($round);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FundingStoreRequest $request, string $id)
    {
        $round = auth()->user()->company->fundingRounds()->findOrFail($id);
        $round->update($request->validated());
        if($round) {
            return redirect()->route('company.profile_view_mine')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
        }
        else {
            return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

//            if ($zoomid) {
//                $zoom = $this->delete($zoomid);
//                if (!$zoom['success']) {
//                    return response()->json(['state' => 'failed'], 500);
//                }
//            }
            (auth()->user()->company?->fundingRounds()->findOrFail($id) ?? abort(404))->delete();
            return response()->json(['state' => 'success'], 200);

    }
}
