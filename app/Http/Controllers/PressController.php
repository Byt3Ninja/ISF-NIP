<?php

namespace App\Http\Controllers;
use App\Http\Requests\StorePressRequest;
use App\Http\Requests\UpdatePressRequest;
use App\Press;
use DB;
use Log;
use Exception;
use Session;
use App\Services\FileManagerService;

class PressController extends Controller
{

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(){
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$press = Press::all();
		return view('admin/settings/press/index', compact('press'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$press = Press::all();
		return view('admin/settings/press/create', compact('press'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StorepressRequest $request)
	{
		if ($request->hasFile('img_url'))
		{
			//store the image
			$img_url = $request->file('img_url');
			//get the path of the stores image
			$img_url = app(FileManagerService::class)->upload($img_url, 'press', 'public_uploads', 'image');
		} else { $img_url = ''; }
		$user =auth()->user()->id;
		$result = Press::create($request->except(['img_url','user_added']) + ['img_url' => $img_url,'user_added' => $user]);
		if($result)
			return redirect()->route('press.index')->with( ['msg' => 'StoredSuccessfully'] );
		else
			return back()->withInput()->with( ['msg' => 'FailedStore'] );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\press  press
	 * @return \Illuminate\Http\Response
	 */
	public function show(Press $press)
	{
		// abort(401);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\press  $press
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Press $press)
	{
		return view('admin/settings/press/edit', compact('press'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\press  $press
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdatePressRequest $request, Press $press)
	{
		$user =auth()->user()->id;
		if ($request->hasFile('img_url')) {
			//store the image
			$img_url = $request->file('img_url');
			//get the path of the stores image
			$img_url = app(FileManagerService::class)->upload($img_url, 'press', 'public_uploads', 'image');
			//update

			$result = $press->update($request->except(['img_url','user_added']) + ['img_url' => $img_url,'user_added'=>$user]);

		}else{
			$result = $press->update($request->except(['user_added'])+ ['user_added'=>$user]);
		}

		if($result)
			return redirect()->route('press.index')->with( ['msg' => __('StoredSuccessfully')] );
		else
			return back()->withInput()->with( ['msg' => 'FailedStore'] );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\press  $press
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Press $press)
	{
		$press->delete();
		return response()->json([
			'state' => 'success'
		], 200);
	}
}
