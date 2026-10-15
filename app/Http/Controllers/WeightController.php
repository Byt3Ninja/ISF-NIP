<?php

namespace App\Http\Controllers;

use App\Stage;
use App\Weight;
use App\Category;
use App\Question;
use App\SubCategory;
use App\QuestionsBank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WeightController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $weights = Weight::with(['stage', 'category', 'subcategory', 'question'])->get();
        $stages = Stage::all();
        $categories = Category::where('category_type', 'Assesment')->get();
        $subcategories = SubCategory::where('Type', 'Assesment')->get();
        $questions = QuestionsBank::where('group', 'assessment')->get();

        if($request->category_id) {
            $weights = Weight::where('category_id', $request->category_id)->with(['stage', 'category', 'subcategory', 'question'])->get();
           
        }
        if($request->subcategory_id) {
            $weights = Weight::where('subcategory_id', $request->subcategory_id)->with(['stage', 'category', 'subcategory', 'question'])->get();
           
        }

        return view('admin.weights', compact('weights', 'stages', 'categories', 'subcategories', 'questions'));
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
        abort_unless(auth()->user()->ableTo('weights.store'), 403);
        $request->validate([
            'stage_id' => 'required|exists:stages,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:sub_categories,id',
            // 'question_id' => 'exists:questions_bank,id',
            'weights' => 'required|numeric',
        ]);

       
        $Weights = new Weight();
        $Weights->stage_id = $request->stage_id;
        $Weights->category_id = $request->category_id;
        $Weights->subcategory_id = $request->subcategory_id;
        // $Weights->question_id = $request->question_id;
        $Weights->weight = $request->weights;
        $Weights->save();

        return redirect()->route('weights.index')->with(['class'=>'success', 'message'=>__('StoredSuccessfully')]);
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
        abort_unless(auth()->user()->ableTo('weights.update'), 403);
        $request->validate([
            'stage_id' => 'required|exists:stages,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:sub_categories,id',
            // 'question_id' => 'exists:questions_bank,id',
            'weights' => 'required|numeric',
        ]);

        $Weights = Weight::findOrFail($id);
        $Weights->stage_id = $request->stage_id;
        $Weights->category_id = $request->category_id;
        $Weights->subcategory_id = $request->subcategory_id;
        // $Weights->question_id = $request->question_id;
        $Weights->weight = $request->weights;
        $Weights->save();

        return redirect()->route('weights.index')->with(['class'=>'success', 'message'=>__('UpdatedSuccessfully')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {   
        abort_unless(auth()->user()->ableTo('weights.destroy'), 403);
        $Weights = Weight::findOrFail($id);
        $Weights->delete();
        return redirect()->route('weights.index')->with(['class'=>'success', 'message'=>__('DeletedSuccessfully')]);
    }
}
