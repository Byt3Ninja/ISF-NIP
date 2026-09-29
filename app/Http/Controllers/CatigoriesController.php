<?php

namespace App\Http\Controllers;

use App\Category;
use App\SubCategory;
use Illuminate\Http\Request;

class CatigoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $categories = Category::all();
        
        // Return all categories
        return view('admin.TimeSheets.category', compact('categories'));
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
            'category_name_ar' => 'required',
            'category_name_en' => 'required',
            'category_type' => 'required',
        ]);
        $tags = new Category();
        $tags->category_name_ar = $request->category_name_ar;
        $tags->category_name_en = $request->category_name_en;
        $tags->category_description = $request->category_description;
        $tags->category_type = $request->category_type;
        $tags->save();
    
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
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
            'category_name_ar' => 'required',
            'category_name_en' => 'required',
            'category_type' => 'required',
        ]);
        $tags = Category::find($id);
        if($tags->category_type != $request->category_type){
            $subCategories = SubCategory::where('category_id', $tags->id)->get();
            foreach($subCategories as $subCat){
                $subCat->update([
                    'Type' => $request->category_type ?? null, // في حال لم يتم العثور على نوع
                ]);
            }
        }
        
        
        
        $tags->category_name_ar = $request->category_name_ar;
        $tags->category_name_en = $request->category_name_en;
        $tags->category_description = $request->category_description;
        $tags->category_type = $request->category_type;
        $tags->save();

        
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Category = Category::find($id);
        // التأكد من وجود الـ Tag
        if (!$Category) {
            return back()->with(['class' => 'danger', 'message' => __('CategoryNotFound')]);
        }
        // حذف السجل بشكل دائم
        $Category->forceDelete();
        // إرجاع استجابة ناجحة
        return back()->with(['class' => 'success', 'message' => __('DeletedSuccessfully')]);
            
        
    }
}
