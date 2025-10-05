<?php

namespace App\Http\Controllers;

use App\StaticCategory;
use Illuminate\Http\Request;

class IndustryController extends Controller
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
        $request->validate([
				'name' => 'required',
			]);
			$industry = new StaticCategory();
			$industry->name = $request->name;
			$industry->save();
			
			return response()->json(['status'=> 'success','message' => 'AddedSuccessfully']);
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
        $id = $request->input('id');
			$name = $request->input('newName');

			$updateindustry = StaticCategory::where('id', $id)->first();
			$updateindustry->name = $name;
			$updateindustry->save();

			return response()->json(['message' => 'UpdatedSuccessfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleteindustry = StaticCategory::where('id', $id)->delete();
			return response()->json(['message' => 'DeletedSuccessfully']);
    }
}
