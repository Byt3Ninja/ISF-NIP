<?php

namespace App\Http\Controllers;

use App\Sector;
use App\StaticCategory;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
       $request->validate([
				'name' => 'required',
				'industryId' => 'required|exists:static_categories,id',
			]);
			$sector = new Sector();
			$sector->name_en = $request->name;
			$sector->name_ar = $request->name;
			$sector->save();

			$industry = StaticCategory::where('id', $request->industryId)->first();
			$industry->sectors()->attach($sector->id);
			$industry->save();
			
			return response()->json(['message' => 'AddedSuccessfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
				'newName' => 'required',
				'industryId' => 'required|exists:static_categories,id',
			]);

			$name = $request->input('newName');
			$industry_id = $request->input('industryId');

			$updateSettings = Sector::where('id', $id)->first();
			$updateSettings->name_en = $name;
			$updateSettings->name_ar = $name;
			$updateSettings->save();

			// $industry = StaticCategory::where('id', $industry_id)->first();
			// $industry->sectors()->updateExistingPivot($id, ['name_en' => $name, 'name_ar' => $name]);
			// $industry->save();

			return response()->json(['message' => 'UpdatedSuccessfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sector = Sector::find($id);
			if(empty($sector)){
				return response()->json(['message' => 'Not_Found']);
			}
			$sector->delete();
			return response()->json(['message' => 'DeletedSuccessfully']);
    }
}
