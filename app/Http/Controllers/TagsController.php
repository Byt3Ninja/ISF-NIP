<?php

namespace App\Http\Controllers;

use App\Tags;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tags::orderBy("id","desc")->get();

        
            
            
        return view("admin.Tags", compact("tags"));
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
            'tags_name' => 'required',
        ]);
        $user = auth()->user();
        $tags = new Tags();
        $tags->name = $request->tags_name;
        $tags->user_id = $user->id;
        $tags->save();
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tags $tags)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tags $tags)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tags_name' => 'required',
        ]);
        
        $tags = Tags::find($id);
        $tags->name = $request->tags_name;
        $tags->save();
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tags = Tags::find($id);
// التأكد من وجود الـ Tag
if (!$tags) {
    return back()->with(['class' => 'danger', 'message' => __('TagNotFound')]);
}
// حذف السجل بشكل دائم
$tags->forceDelete();
// إرجاع استجابة ناجحة
return back()->with(['class' => 'success', 'message' => __('DeletedSuccessfully')]);
    }


}
