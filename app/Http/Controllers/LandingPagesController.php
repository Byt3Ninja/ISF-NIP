<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Organization;
use Illuminate\Http\Request;

class LandingPagesController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * List custom landing pages.
	 */
	public function index(Request $request)
	{
		$_user = auth()->user();
		
		return view('admin.landing-pages.index');
	}
  
  public function create(Request $request)
  {
    $cycles = Cycle::all();
    
    return view('admin.landing-pages.create', compact('cycles')); 
  }

  public function edit(Request $request)
  {
    $cycles = Cycle::all();
    
    return view('admin.landing-pages.edit', compact('cycles'));
  }


	/**
	 * Store a landing page section.
	 *
	 * Temporary stub: always returns a successful response so the
	 * front-end section save never hits a "route not found" error.
	 *
	 * @param  string  $section
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateSection(Request $request, $section)
	{
		return response()->json(['success' => true]);
	}
}
