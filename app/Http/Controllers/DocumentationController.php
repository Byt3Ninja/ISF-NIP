<?php

namespace App\Http\Controllers;

use App\Documentation;
use App\Services\HtmlSanitizer;
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
        $request->validate([
            'menu_ar' => 'required|string',
            'menu_en' => 'required|string',
            'arr' => 'required|integer',
            'role' => 'required|string',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
        ]);
        // Strip any JavaScript/XSS vectors; content is rendered raw on the documentation page.
        $request->merge([
            'content_ar' => HtmlSanitizer::clean($request->content_ar),
            'content_en' => HtmlSanitizer::clean($request->content_en),
        ]);
        $user = Auth::id();
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
        $request->validate([
            'menu_ar' => 'required|string',
            'menu_en' => 'required|string',
            'arr' => 'required|integer',
            'role' => 'required|string',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
        ]);
        // Strip any JavaScript/XSS vectors; content is rendered raw on the documentation page.
        $request->merge([
            'content_ar' => HtmlSanitizer::clean($request->content_ar),
            'content_en' => HtmlSanitizer::clean($request->content_en),
        ]);
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
