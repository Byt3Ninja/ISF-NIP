<?php

namespace App\Http\Controllers;

use App\Expertise;
use Illuminate\Http\Request;

class ExpertiseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $expertises = Expertise::where('parent_id', null)->with('children')
        //             ->orderBy('id', 'desc')
        //             ->get();
        
        // $expertises = $expertises->map(function ($parent) {
        //     return [
        //         'id' => $parent->id,
        //         'title' => $parent->title,
        //         'children' => $parent->children->map(function ($child) use ($parent) {
        //             return [
        //                 'id' => $child->id,
        //                 'title' => $child->title,
        //                 'parent' => $parent->id 
        //             ];
        //         })->toArray(), 
        //     ];
        // });
        
        // $parent_expertises = Expertise::where('parent_id', null)->get();
        // $child_expertises = Expertise::where('parent_id', '!=', null)->get();

        // dd($expertises);
        
        return view('admin.settings.expertise.index');
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
            'title' => 'required|string',
            'parent' => 'nullable|exists:expertises,id',
        ]);
        $addExpertise = new Expertise();
        $addExpertise->title = $request->input('title');
        
        $addExpertise->parent = $request->input('parent') ?? null;
        $addExpertise->save();
        return response()->json($addExpertise);
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
     
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'parent' => 'nullable|exists:expertises,id',
        ]);
        $updateExpertise = Expertise::findOrFail($id);
        $updateExpertise->title = $request->input('title');
        $updateExpertise->parent = $request->input('parent') ?? null;
        $updateExpertise->save();
        return response()->json($updateExpertise);
    }

    public function destroy($id)
    {
        $destroyExpertise = Expertise::findOrFail($id);
        $exprtis = Expertise::where('parent', $destroyExpertise->id)->delete();
        $destroyExpertise->delete();
        return response()->json($destroyExpertise);
    }

}
