<?php

namespace App\Http\Controllers;
use App\Organization;
use App\QuestionCategories;
use Illuminate\Http\Request;

class QuestionCategoriesController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() { $this->middleware('auth'); }

	public function index()
	{
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
		$categories = QuestionCategories::with(['organization']);
		if( !empty( $orgIds ) && !$user->hasRole('admin') ) { $categories = $categories->whereIn('organization_id', $orgIds); }
		$categories = $categories->orderBy('id', 'desc')->get();

		return view('admin.QuestionCategories.index', compact('categories'));
	}

	public function create()
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$organizations = Organization::query();
		if( !empty( $orgIds ) && !$user->hasRole('admin') ) { $organizations = $organizations->whereIn('id', $orgIds); }
		$organizations = $organizations->get();
		return view('admin.QuestionCategories.create', compact('organizations'));
	}

	public function store(Request $request)
	{
		$this->validate($request, ['organization_id' => 'required|exists:organizations,id', 'title' => 'required|string|max:255']);
		
		$category = new QuestionCategories;
		$category->organization_id = $request->organization_id;
		$category->title = $request->title;
		$category->save();

		return redirect()->route('question-categories.index')->with('success', 'StoredSuccessfully');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit( $id )
	{
		$category = QuestionCategories::find( $id );
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$organizations = Organization::query();
		if( !empty( $orgIds ) && !$user->hasRole('admin') ) { $organizations = $organizations->whereIn('id', $orgIds); }
		$organizations = $organizations->get();
		return view('admin.QuestionCategories.edit', compact('category', 'organizations'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Type  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function update($id, Request $request)
	{
		$this->validate($request, ['organization_id' => 'required|exists:organizations,id', 'title' => 'required|string|max:255']);
		$category = QuestionCategories::find( $id );
		$category->organization_id = $request->organization_id;
		$category->title = $request->title;
		$category->save();

		return redirect()->route('question-categories.edit', ['question_category' => $id])->with('success', 'StoredSuccessfully');
	}

	public function destroy( $id )
	{
		$category = QuestionCategories::find( $id );
		if( !empty( $category ) ) { $category->delete(); }
	}
}
