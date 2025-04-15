<?php

namespace App\Http\Controllers;

use App\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DocumentationController extends Controller
{

    public function index()
    {
        $docs = Documentation::orderBy('arr','ASC')->get();
        return view('admin/settings/documentations/index' , compact('docs'));
    }


    public function create()
    {
        return view('admin/settings/documentations/create');
    }


    public function store(Request $request)
    {
        $user =Auth::id();
        $result = Documentation::create($request->except(['user_id']) + ['user_id' => $user]);
        if($result)
            return redirect()->route('documentation.index')->with( ['msg' => 'StoredSuccessfully'] );
        else
            return back()->withInput()->with( ['msg' => 'FailedStore'] );
    }


    public function show($id=1)
    {
        $doc = Documentation::findOrfail($id);
        $menu = Documentation::orderBy('arr','ASC')->where('id',$id)->get(['id','menu_ar','menu_en']);
        return view('admin.documentation', compact('doc','menu'));
    }


    public function edit(Documentation $documentation)
    {
        return view('admin/settings/documentations/edit', compact('documentation'));
    }

    public function update(Request $request, Documentation $documentation)
    {
        $user =auth()->id();
        $result = $documentation->update($request->except(['user_id'])+ ['user_id'=>$user]);
        if($result)
            return redirect()->route('documentation.index')->with( ['msg' => __('StoredSuccessfully')] );
        else
            return back()->withInput()->with( ['msg' => 'FailedStore'] );
    }

    public function destroy(Documentation $doc)
    {
        $doc->delete();
        return response()->json(['state' => 'success'], 200);
    }
}
