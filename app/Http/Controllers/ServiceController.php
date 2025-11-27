<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;

use App\Notifications\TemplateEmail;
use App\Organization;
use App\Service;
use App\ServiceCategory;
use App\serviceProviders;

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
		$user = auth()->user();
		$services = Service::with('serviceCategory')
			->when($user->hasRole('serviceProvider'), function ($query) use ($user) {
				$provider_id = $user?->service_provider?->id ?? 0;
				return $query->where('service_provider_id', $provider_id);
			})
		->get();
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
		$organizations = Organization::all();
		$providers = serviceProviders::all();
		return view('admin/settings/services/create', compact('serviceCategories','Setting','organizations', 'providers'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreServiceRequest $request)
	{
		$user = auth()->user();
		$category = ServiceCategory::find( $request->service_category_id );
		if( empty( $category ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }

		$provider = !$user->hasRole('serviceProvider') ? serviceProviders::find( $request->service_provider_id ) : $user?->service_provider;
		if( empty( $provider ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] );  }

		if( $request->hasFile('img_url') ){
			//store the image
			$img_url = $request->file('img_url');
			//get the path of the stores image
			$img_url = $img_url->store('services', ['disk' => 'public_uploads']);
		} else { $img_url = '';  }
		$inputs = $request->except(['img_url']);
		$inputs['img_url'] = $img_url;

		$inputs = $request->except(['img_url']) + [
			'img_url' => $img_url,
			'vendor_name' => $provider->company_name,
			'vendor_phone' => $provider->phone,
			'vendor_email' => $provider->email,
			'service_provider_id' => $provider->id,
			'hide_price' => $request->has('hide_price') ? 1 : 0, 
			'hide_vendor_info' => $request->has('hide_vendor_info') ? 1 : 0
		];

		$inputs['status'] = 1;
		$inputs['name_ar'] = $inputs['name_en']; 
		$inputs['description_ar'] = $inputs['description_en'];

		$service = Service::create($inputs);

		// Assign organizations (if selected)
		if ($service && $request->has('organization_ids')) {
			$service->organizations()->attach($request->organization_ids);
		}
		
		if ($service) {
			return redirect()->route('services.index')->with(['msg' => 'ServiceAdded']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedStore']);
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
		$organizations = Organization::all();
		$providers = serviceProviders::all();
		return view('admin/settings/services/edit', compact('service', 'serviceCategories', 'organizations', 'providers'));
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
		$user = auth()->user();
		$category = ServiceCategory::find($request->service_category_id);
		if( empty( $category ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] );  }

		$provider = !$user->hasRole('serviceProvider') ? serviceProviders::find( $request->service_provider_id ) : $user?->service_provider;
		if( empty( $provider ) ) { return back()->withInput()->with( ['msg' => 'FailedStore'] );  }

		$inputs = $request->except(['img_url']);

		// Handle Image Upload
		if ($request->hasFile('img_url')) {
			$img_url = $request->file('img_url')->store('services', ['disk' => 'public_uploads']);
			$inputs['img_url'] = $img_url;
		}

		$inputs['service_provider_id'] = $provider->id;
		$inputs['vendor_name'] = $provider->company_name;
		$inputs['vendor_phone'] = $provider->phone;
		$inputs['vendor_email'] = $provider->email;

		$inputs['hide_price'] = $request->input('hide_price', 0);
		$inputs['hide_vendor_info'] = $request->input('hide_vendor_info', 0);
		$inputs['name_ar'] = $inputs['name_en']; 
		$inputs['description_ar'] = $inputs['description_en'];

		$inputs['status'] = $request->input('status', 1);

		$updated = $service->update($inputs);

		if ($request->has('organization_ids')) {
			$service->organizations()->sync($request->organization_ids);
		} else {
			$service->organizations()->detach();
		}

		if ($updated) {
			return redirect()->route('services.index')->with(['msg' => 'ServiceUpdated']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedUpdate']);
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

	public function requests(Request $request)
	{
		$received = ServicesRequests::whereNull('approved')->with('service');
		$countold = ServicesRequests::whereNotNull('approved')->count();

		if($request->Service_id != 0){
			$received = $received->whereNull('approved')->where('service_id', $request->Service_id)->with('service');
		}
		if($request->status != 0){
			$request->status == 'Done' ? $status = 1 : $status = 0;
			
			$received = $received->whereNull('approved')->where('done', $status)->with('service');
		}
		if($request->start_date != 0){
			$received = $received->whereNull('approved')->where('date_time', '>=', $request->start_date)->with('service');
		}
		if($request->end_date != 0){
			$received = $received->whereNull('approved')->where('date_time', '<=', $request->end_date)->with('service');
		}


		$received = $received->orderBy('created_at', 'desc')->get();
		
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
            if( !in_array(env('APP_ENV', 'local'), ['local']) )
            {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }
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
