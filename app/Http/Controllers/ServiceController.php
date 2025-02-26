<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;

use App\Notifications\TemplateEmail;
use App\Service;
use App\ServiceCategory;

use App\ServicesRequests;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Exception;
use Session;

class ServiceController extends Controller
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
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$services = Service::with('serviceCategory')->get();
		$Setting = \App\Settings::where('id', 1)->first();
		return view('admin/settings/services/index', compact('services','Setting'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$serviceCategories = ServiceCategory::all();
		$Setting = \App\Settings::where('id', 1)->first();
		return view('admin/settings/services/create', compact('serviceCategories','Setting'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreServiceRequest $request)
	{
		$category = ServiceCategory::find( $request->service_category_id );
		if( empty( $category ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
		else
		{
			if( empty( $category->organization_id ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
			else
			{
				if( $request->hasFile('img_url') )
				{
					//store the image
					$img_url = $request->file('img_url');
					//get the path of the stores image
					$img_url = $img_url->store('services', ['disk' => 'public_uploads']);
				} else { $img_url = ''; }
				$inputs = $request->except(['img_url']) + ['img_url' => $img_url];
				$inputs['organization_id'] = $category->organization_id;
				$result = Service::create( $inputs );

				if( $result ) { return redirect()->route('services.index')->with( ['msg' => 'StoredSuccessfully'] ); }
				else { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
			}
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Service  $Service
	 * @return \Illuminate\Http\Response
	 */
	public function show(Service $Service)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Service  $Service
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Service $service)
	{
		$serviceCategories = ServiceCategory::all();
		return view('admin/settings/services/edit', compact('service', 'serviceCategories'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Service  $Service
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdateServiceRequest $request, Service $service)
	{
		$category = ServiceCategory::find( $request->service_category_id );
		if( empty( $category ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
		else
		{
			if( empty( $category->organization_id ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
			else
			{
				if( $request->hasFile('img_url') )
				{
					// store the image
					$img_url = $request->file('img_url');
					// get the path of the stores image
					$img_url = $img_url->store('services', ['disk' => 'public_uploads']);
					$inputs = $request->except(['img_url']) + ['img_url' => $img_url];
				} else { $inputs = $request->all(); }
				$inputs['organization_id'] = $category->organization_id;
				// update
				$result = $service->update($inputs);

				if( $result ) { return redirect()->route('services.index')->with( ['msg' => 'StoredSuccessfully'] ); }
				else { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
			}
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Service  $Service
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Service $service)
	{
		$service->delete();
		return response()->json(['state' => 'success'], 200);
	}

	public function requests()
	{
		$received = ServicesRequests::whereNull('approved')->get();
		$countold = ServicesRequests::whereNotNull('approved')->count();
		return view('admin.settings.services.requests', compact('received','countold') );
	}

	public function oldrequests()
	{
		$received = ServicesRequests::whereNotNull('approved')->get();
		return view('admin.settings.services.oldrequests', compact('received') );
	}

	public function approve(Request $request)
	{
		if( $request )
		{
			ServicesRequests::where('id', $request->id)->update(['approved'=>$request->stat,'admin_comment'=>$request->comment,'admin'=>Auth::id()]);
			/// notify user via Mail
			$crequest = ServicesRequests::findOrfail($request->id);
			$request->stat=="Yes" ? $stat = "approved" : $stat = "disapproved";
			$user = new User();
			$user->email = $crequest->user->email;
			$user->id = $crequest->user_id;
			$rfullname = $crequest->user->first_name_en ." ". $crequest->user->last_name_en;
			$MSG = new TemplateEmail;
			$MSG->Message=["Service request - VI portal","Dear ".$rfullname.",", "Kindly note that your service request (".$crequest->service->name_en . ") has been ".$stat.", Check your account for more details.","/dashboard/requests/mine"];
			$user->notify($MSG);
			/// End of mail send
			return response()->json(['state'=>'success']);
		}
		return response()->json(['state' => 'failed'], 500);
	}

	public function myrequests()
	{
		$received = ServicesRequests::join('services','service_id','services.id')->where('user_id',Auth::id())->orderBy('services_requests.created_at','DESC')
			->get(['services_requests.id as id','services.id as sid','services.name_en as title_en','services.name_ar as title_ar', 'services_requests.comment'
				, 'services_requests.approved','services_requests.admin_comment','services_requests.created_at']);
		return view('admin.clients.myrequests', compact('received') );
	}
}
