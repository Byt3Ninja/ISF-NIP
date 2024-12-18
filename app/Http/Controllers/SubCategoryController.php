<?php

namespace App\Http\Controllers;

use App\Category;
use App\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subCategories = SubCategory::with('category')->orderBy('id', 'DESC')->get();
        $categories = Category::all();

        
        return view('admin.subcategories', compact('subCategories','categories'));
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
            'subcategory_name_ar' => 'required|string|max:255',
            'subcategory_name_en' => 'required|string|max:255',
            'category' => 'required|exists:categories,id',
        ]);

        $idCategory = $request->input('category');
        $type = Category::find($idCategory);
        
        SubCategory::create([
            'name_ar' => $request->input('subcategory_name_ar'),
            'name_en' => $request->input('subcategory_name_en'),
            'description' => $request->input('subcategory_description'),
            'Type' => $type->category_type,
            'category_id' => $request->input('category'),
        ]);
        return redirect()->route('subcategories.index')->with('class', 'success')->with('message', 'SubCategory_stored_successfully');
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
    public function update(Request $request, SubCategory $SubCategory)
    {
        
        $request->validate([
            'subcategory_name_ar' => 'required|string|max:255',
            'subcategory_name_en' => 'required|string|max:255',
            'category' => 'required|exists:categories,id',
        ]);

        $idCategory = $request->input('category');
        $type = Category::find($idCategory);

    $subCategory = SubCategory::find($request->input('subcategory_id')); // افترضنا أن هناك ID لتحديد السجل

    if ($subCategory) {
        $subCategory->update([
            'name_ar' => $request->input('subcategory_name_ar'),
            'name_en' => $request->input('subcategory_name_en'),
            'description' => $request->input('subcategory_description'),
            'Type' => $type->category_type ?? null, // في حال لم يتم العثور على نوع
            'category_id' => $idCategory,
        ]);
    }
        
        return redirect()->route('subcategories.index')->with('class', 'success')->with('message', 'SubCategory_updated_successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();
        return redirect()->route('subcategories.index')->with([
        'class'=>'success',
        'success'=>'SubCategory_deleted_successfully']);
    }
}
