<?php

namespace App\Http\Controllers;

use App\Technology;
use Illuminate\Http\Request;

class TechnologyController extends Controller
{
    public function index()
    {
        $technologies = Technology::all();
        return view('technologies.index', compact('technologies'));
    }

    public function create()
    {
        return view('technologies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Technology::create($request->all());

        return redirect()->route('technologies.index')->with('success', 'Technology created successfully.');
    }

    public function edit(Technology $technology)
    {
        //
    }

    public function update(Request $request, Technology $technology)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $technology->update($request->all());

        return redirect()->route('technologies.index')->with('success', 'Technology updated successfully.');
    }

    public function destroy(Technology $technology)
    {
        $technology->delete();
        return redirect()->route('technologies.index')->with('success', 'Technology deleted successfully.');
    }

    public function create_technologies(Request $request)
		{
			$request->validate([
				'name' => 'required',
			]);
			$technology = new Technology();
			$technology->name = $request->name;
			$technology->save();

			
			return response()->json(['message' => 'AddedSuccessfully']);
		} 
		public function update_technologies(Request $request, $id)
		{
			
			$name = $request->input('newName');

			$updateSittings = Technology::where('id', $id)->first();
			$updateSittings->name = $name;
			$updateSittings->save();

			return response()->json(['message' => 'UpdatedSuccessfully']);
		}
		public function delete_technologies(Request $request, $id)
		{
			$updateSittings = Technology::where('id', $id)->first();
			$updateSittings->delete();

			return response()->json(['message' => 'DeletedSuccessfully']);
		}
}
