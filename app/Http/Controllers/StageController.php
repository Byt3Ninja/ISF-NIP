<?php

namespace App\Http\Controllers;

use App\Stage;
use App\Weight;
use App\QuestionsBank;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stages = Stage::all();
        return view('admin.clients.stages', compact('stages'));
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
            'stage_name_ar' => 'required',
            'stage_name_en' => 'required',
        ]);
        $stages = new Stage();
        $stages->name_ar = $request->stage_name_ar;
        $stages->name_en = $request->stage_name_en;
        $stages->description_ar = $request->stage_Description_ar;
        $stages->description_en = $request->stage_Description_en;
        $stages->save();

        $categorySubcategoryPairs = QuestionsBank::where('group', 'assessment')
            ->whereNotNull('ascategory_id')
            ->whereNotNull('subcategory_id')
            ->get(['ascategory_id', 'subcategory_id'])
            ->unique(fn($question) => $question->ascategory_id . '-' . $question->subcategory_id);

        if ($categorySubcategoryPairs->isNotEmpty()) {
            $now = now();
            Weight::insert($categorySubcategoryPairs->map(fn($pair) => [
                'stage_id' => $stages->id,
                'category_id' => $pair->ascategory_id,
                'subcategory_id' => $pair->subcategory_id,
                'weight' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all());
        }

        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('Stage_stored_successfully')]);
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
            'stage_name_ar' => 'required',
            'stage_name_en' => 'required',
        ]);
        $stages = Stage::find($id);
        $stages->name_ar = $request->stage_name_ar;
        $stages->name_en = $request->stage_name_en;
        $stages->description_ar = $request->stage_Description_ar;
        $stages->description_en = $request->stage_Description_en;
        $stages->save();
    
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('Stage_updated_successfully')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stages = Stage::find($id);
        $stages->delete();
        return back()->with(['class'=>'success','message'=>__('Stage_deleted_successfully')]);
    }
}
