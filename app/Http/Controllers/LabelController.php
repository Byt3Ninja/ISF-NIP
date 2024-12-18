<?php

namespace App\Http\Controllers;

use App\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labels = Label::orderBy("id","desc")->get();

        
            
            
        return view("admin.Label", compact("labels"));
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
            'labels_name' => 'required',
        ]);
        $user = auth()->user();
        $labels = new Label();
        $labels->name = $request->labels_name;
        $labels->user_id = $user->id;
        $labels->save();
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Label $label)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Label $label)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'labels_name' => 'required',
        ]);
        
        $labels = Label::find($id);
        $labels->name = $request->labels_name;
        $labels->save();
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Label $label)
    {
        //
    }
}
