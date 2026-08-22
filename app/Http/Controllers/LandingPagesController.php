<?php

namespace App\Http\Controllers;

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
	 * List organizations' custom landing pages.
	 */
	public function index(Request $request)
	{
		$_user = auth()->user();
		
		return view('admin.landing-pages.index');
	}
}
