<?php

namespace App\Http\Controllers;

use App\Events\WebMessage;
use Illuminate\Http\Request;
use App\Http\Requests\{StoreServiceRequest, UpdateServiceRequest};
use App\Notifications\{TemplateEmail, ServiceRequestNotification};
use App\{User, Settings, Currency, Service, Organization, ServiceCategory, serviceProviders, ServicesRequests};
use Illuminate\Support\Facades\{Auth, Notification};
use DB, Log, Exception, Session;

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
		$Setting = Settings::where('id', 1)->first();
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
		$Setting = Settings::where('id', 1)->first();
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
		$user = auth()->user();
		$received = ServicesRequests::whereNull('approved')->with('service');
		$countold = ServicesRequests::whereNotNull('approved')->when($user->hasRole('serviceProvider'), function ($q) use ($user) {
			$provider_id = $user->service_provider->id;
			return $q->whereHas('service', function ($q2) use ($provider_id) {
				$q2->where('service_provider_id', $provider_id);
			});
		})->count();

		$received = $received->when($user->hasRole('serviceProvider'), function ($q) use ($user) {
			$provider_id = $user->service_provider->id;
			return $q->whereHas('service', function ($q2) use ($provider_id) {
				$q2->where('service_provider_id', $provider_id);
			});
		});

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
		$user = auth()->user();
		$received = ServicesRequests::whereNotNull('approved')->when($user->hasRole('serviceProvider'), function ($q) use ($user) {
			$provider_id = $user->service_provider->id;
			return $q->whereHas('service', function ($q2) use ($provider_id) {
				$q2->where('service_provider_id', $provider_id);
			});
		})->get();
		return view('admin.settings.services.oldrequests', compact('received') );
	}

	public function serviceAction(Request $request)
	{
		if( $request )
		{
			$this->validate($request, [
				'id' => 'required|exists:services_requests,id',
				'stat' => 'required|in:approve,reject',
			]);

			$user = auth()->user();
			$serviceRequest = ServicesRequests::where('id', $request->id);
			$serviceRequest = $serviceRequest->when($user->hasRole('serviceProvider'), function ($q) use ($user) {
				$provider_id = $user->service_provider->id;
				return $q->whereHas('service', function ($q2) use ($provider_id) {
					$q2->where('service_provider_id', $provider_id);
				});
			});
			$serviceRequest = $serviceRequest->first();
			if( empty( $serviceRequest ) ) { return response()->json(['status' => false, 'title' => 'Not Found', 'message' => 'Service request not found']); }

			$stat = trim( strtolower( $request->stat ) );
			$serviceRequest->approved = $stat == 'approve' ? 'Yes' : 'No';
			$serviceRequest->admin_comment = $request->input('comment', null);
			$serviceRequest->admin = $user->id;
			$serviceRequest->save();

			/// notify user via Mail
			$stat .= 'ed';
			$user = new User();
			$user->email = $serviceRequest->user->email;
			$user->id = $serviceRequest->user_id;
			$rFullname = $serviceRequest->user->first_name_en ." ". $serviceRequest->user->last_name_en;
			$MSG = new TemplateEmail;
			$MSG->Message=["Service request - VI portal","Dear ".$rFullname.",", "Kindly note that your service request (".$serviceRequest->service->name_en . ") has been ".$stat.", Check your account for more details.","/dashboard/requests/mine"];
			if( !in_array(env('APP_ENV', 'local'), ['local']) )
			{
				try {
					$user->notify($MSG);
				} catch (\Throwable $th) {
					// throw $th;
				}
			}
			/// End of mail send
			return response()->json(['status' => true, 'title' => 'Success', 'message' => 'Service request ' . $stat . ' successfully']);
		}
		return response()->json(['status' => false, 'title' => 'Failed', 'message' => 'This request cannot be processed right now'], 500);
	}

	public function availableServices(Request $request)
	{
		$t = $request->input('type');
		$find = $request->input('find');
		$services = Service::with('serviceCategory')
			->where('status', true)
			->whereHas('organizations')
		->get();

		$services = $services->map(function ($service) {
			$service->category_name_en = $service->serviceCategory->name_en ?? null;
			$service->category_name_ar = $service->serviceCategory->name_ar ?? null;
			return $service;
		});

		$setting = Settings::where('id', 1)->first();
		$currency = Currency::where('id', $setting->Currency)->first();
		return view('admin.clients.directory')->with('services', $services)->with('setting', $setting)->with('currency', $currency);
	}

	public function details($id, Request $request)
	{
		if( $request && $id )
		{
			$service = Service::where('id', $id)->where('status', true)->first();
			if( empty( $service ) ) { return response()->json(['status' => false, 'title' => 'Not Found', 'message' => 'Service not found']); }

			$rate = (int) $service->myRate();
			$service->rate = $rate;
			return response()->json(['status' => true, 'title' => 'Success', 'service' => $service]);
		}
		return response()->json(['status' => false, 'title' => 'Failed', 'message' => 'This request cannot be processed right now'], 500);
	}

	public function rate($id, Request $request)
	{
		if( $request && $id )
		{
			$serviceRequest = ServicesRequests::where('id', $id)->first();
			if( empty( $serviceRequest ) )
			{
				return redirect()->back()->with(['class' => 'danger', 'msg' => __('admin.Service request not found')]);
			}
			$service = $serviceRequest->service;
			if( empty( $service ) ) { return redirect()->back()->with(['class' => 'danger', 'msg' => __('admin.Service not found')]); }
			if( !$service->status ) { return redirect()->back()->with(['class' => 'danger', 'msg' => __('admin.Service not found')]); }

			$is_done = $request->input('is_service_done_' . $id, null) ?? null;
			$rate = $request->input('service_rate_' . $id, null) ?? null;
			$rate_comment = $request->input('service_comment_' . $id, null) ?? null;

			$done = trim( strtolower( $is_done ) ) == 'yes' ? 1 : ( trim( strtolower( $is_done ) ) == 'no' ? 1 : null);

			ServicesRequests::updateOrInsert(['id' => $id], ['rate' => $rate, 'rate_comment' => $rate_comment, 'done' => $done]);
			return redirect()->back()->with(['class' => 'success', 'msg' => __('admin.Service request rated, thank you for your feedback')]);
		}
		return redirect()->back()->with(['class' => 'danger', 'msg' => __('admin.An unexpected error occurred, please try again later')]);
	}

	public function book_service(Request $request)
	{
		if( $request )
		{
			$user = auth()->user();
			$service = Service::find( $request->id );
			if( empty( $service ) ) { return response()->json(['state' => false, 'title' => 'Not Found', 'message' => 'Service not found']); }

			$serviceRequest = ServicesRequests::create([
				'service_id' => $service->id,
				'user_id' => $user->id,
				'comment' => $request->input('comment', null) ?? null,
				'date_time' => $request->datetime
			]);

			event(new WebMessage('NewServ', $user->first_name_en . ' ' . $user->last_name_en));

			if( $service && $service->vendor_email && !in_array(env('APP_ENV', 'local'), ['local']) )
			{
				// Create anonymous notifiable for vendor email
				Notification::route('mail', $service->vendor_email)->notify(new ServiceRequestNotification($serviceRequest));
			}

			return response()->json(['state' => true, 'title' => 'Success', 'message' => __('admin.request sent')]);
		}
		return response()->json(['status' => false, 'title' => 'Failed', 'message' => 'This request cannot be processed right now'], 500);
	}

	public function myRequests()
	{
		$received = ServicesRequests::join('services','service_id','services.id')
			->where('user_id', Auth::id())
			->orderBy('services_requests.created_at','DESC')
			->get([
				'services_requests.id as id',
				'services.id as sid',
				'services.name_en as title_en',
				'services.name_ar as title_ar',
				'services_requests.comment',
				'services_requests.approved',
				'services_requests.admin_comment',
				'services_requests.created_at'
			]);

		return view('admin.clients.myrequests', compact('received') );
	}
}
