<?php

namespace App\Http\Controllers;

use App\Events\WebMessage;
use Illuminate\Http\Request;
use App\Exports\ServicesExport;
use DB, Log, Exception, Session;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\{Auth, Notification};
use App\Http\Requests\{BookServiceRequest, StoreServiceRequest, SubmitReviewRequest, UpdateServiceRequest};
use App\Notifications\{TemplateEmail, ServiceRequestNotification};
use Illuminate\Pagination\LengthAwarePaginator;
use App\{User, Settings, Currency, Service, Organization, ServiceCategory, serviceProviders, ServicesRequests};
use App\Services\ServiceRequestStatusLogger;

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
	public function index(Request $request)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$Setting = Settings::where('id', 1)->first();
		$services = Service::with(['serviceCategory', 'organizations', 'requests'])
			->when($user->hasRole('serviceProvider'), function ($query) use ($user) {
				$provider_id = $user?->service_provider?->id ?? 0;
				$query->where('service_provider_id', $provider_id);
			})
			->when($user->hasRole('registered'), function ($query) {
				$query->where('status', true);
			})
			->when(!$user->hasRole('serviceProvider'), function ($query) use ($orgIds, $user) {
				if( $user->hasRole('registered') || $user->hasAnyRole(['subadmin', 'organizationManager']) )
				{
					$query->whereHas('organizations', function ($q) use ($orgIds) {
						$q->whereIn('organizations.id', $orgIds);
					});
				}
				else
				{
					$query->where(function ($q) use ($orgIds) {
						$q->whereHas('organizations', function ($subQ) use ($orgIds) {
							$subQ->whereIn('organizations.id', $orgIds);
						})
						->orWhereDoesntHave('organizations');
					});
				}
			})
			->when($request->has('search') && !empty($request->search), function ($query) use ($request) {
				$query->where(function ($q) use ($request) {
					$searchTerms = explode(' ', strtolower($request->search));
					foreach( $searchTerms as $term)
					{
						$term = '%' . $term . '%';
						$q->where(function ($subQ) use ($term) {
							$subQ->whereRaw('LOWER(name_en) LIKE ?', [$term])
							->orWhereRaw('LOWER(name_ar) LIKE ?', [$term])
							->orWhereRaw('LOWER(description_en) LIKE ?', [$term])
							->orWhereRaw('LOWER(description_ar) LIKE ?', [$term])
							->orWhereRaw('LOWER(vendor_name) LIKE ?', [$term])
							->orWhereHas('serviceCategory', function ($catQ) use ($term) {
								$catQ->whereRaw('LOWER(name_en) LIKE ?', [$term])->orWhereRaw('LOWER(name_ar) LIKE ?', [$term]);
							});
						});
					}
				});
			})
			->when($request->has('org-id') && !empty($request->{'org-id'}) && $request->{'org-id'} !== 'all', function ($query) use ($request) {
				$query->whereHas('organizations', function ($q) use ($request) {
					$q->where('organizations.id', $request->{'org-id'});
				});
			})
			->when($request->has('status') && $request->status !== 'all', function ($query) use ($request) {
				$query->where('status', $request->status);
			})
			->orderBy('created_at', 'desc')
		->get();

		$services = $services->map(function ($service) {
			$rating = $service->requests->whereNotNull('rate')->where('approved', true)->avg('rate') ?? 0;
			$service->rating = $rating;
			return $service;
		})
		->when($request->has('rate') && $request != 'all' && is_numeric($request->rate), function ($query) use ($request) {
			return $query->filter(function($service) use ($request) {
				return $service->rating >= $request->rate && $service->rating <= $request->rate + 0.99;
			})->values();
		});
		// custom pagination
		// $services = new LengthAwarePaginator($services, $services->count(), 9, 1, [
		// 	'path' => LengthAwarePaginator::resolveCurrentPath(),
		// ]);
		$page = request()->get('page', 1);
		$perPage = 9;

		$servicesCollection = collect($services); // لو مش collection
		$total = $servicesCollection->count();

		$services = new LengthAwarePaginator(
			$servicesCollection->forPage($page, $perPage),
			$total,
			$perPage,
			$page,
			[
				'path' => LengthAwarePaginator::resolveCurrentPath(),
				'query' => request()->query(),
			]
		);
		$organizations = $services->pluck('organizations')->flatten()->unique('id')->whereIn('id', $orgIds)->values();
		return view('admin/settings/services/index', compact('services', 'Setting', 'organizations'));
	}
	public function exportExcel(Request $request)
    {	
        return Excel::download(new ServicesExport($request), 'services'.date('Y-m-d H:i:s').'.xlsx');
    }
	public function exportCSV(Request $request)
    {
        return Excel::download(new ServicesExport($request), 'services'.date('Y-m-d H:i:s').'.csv');
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$user = auth()->user();
		$serviceCategories = ServiceCategory::all();
		$Setting = Settings::where('id', 1)->first();
		if ($user->hasAnyRole('Admin|serviceProvider')) {
			$organizations = Organization::all();
		} elseif ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
			$orgIds = $user->organizations_ids;
			$organizations = Organization::whereIn('id', $orgIds)->get();
		} else {
			$organizations = collect();
		}
		$currencies = Currency::all();
		$providers = serviceProviders::all();
		return view('admin/settings/services/create', compact('serviceCategories','Setting','organizations', 'providers', 'currencies'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreServiceRequest $request)
	{
	
	$request->merge([
			'is_active' => $request->has('is_active') ? 1 : 0
		]);
		
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
			'currency' => $request->input('currency', null),
			'hide_vendor_info' => $request->has('hide_vendor_info') ? 1 : 0
		];

		$inputs['status'] = $request->is_active;
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
		$user = auth()->user();
		$serviceCategories = ServiceCategory::all();
		if ($user->hasAnyRole('Admin|serviceProvider')) {
			$organizations = Organization::all();
		} elseif ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
			$orgIds = $user->organizations_ids;
			$organizations = Organization::whereIn('id', $orgIds)->get();
		} else {
			$organizations = collect();
		}
		$currencies = Currency::all();
		$providers = serviceProviders::all();
		return view('admin/settings/services/edit', compact('service', 'serviceCategories', 'organizations', 'providers', 'currencies'));
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
		
		$request->merge([
			'status' => $request->has('status') ? 1 : 0
		]);
		
		$user = auth()->user();
		if( $user->hasAnyRole(['organizationManager', 'subadmin']) && !$user->hasRole('admin') )
		{
			$orgIds = $user->organizations_ids;
			if( $request->has('organization_ids') )
			{
				$organization_ids = collect( $request->organization_ids )->map( function( $id ) { return intval( $id ); } )->toArray();
				
				$diff = array_diff( $organization_ids, $orgIds );
				if( count( $diff ) > 0 )
				{
					return back()->withInput()->with( ['msg' => 'UnauthorizedOrg'] );
				}
			}
			else
			{
				return back()->withInput()->with( ['msg' => 'select_at_least_one_org'] );
			}
		}
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
		$inputs['currency'] = $request->input('currency', null);
		
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
		$locale = app()->getLocale();
		$nameCol = 'name_' . $locale;
		$descCol = 'description_' . $locale;

		// Base query builder with necessary joins
		$baseQuery = DB::table('services_requests')
			->join('services', 'services_requests.service_id', '=', 'services.id')
			->leftJoin('service_providers', 'services.service_provider_id', '=', 'service_providers.id')
			->join('users', 'services_requests.user_id', '=', 'users.id')
			->leftJoin('companies', 'users.company_id', '=', 'companies.id')
			->whereNull('services_requests.deleted_at')
			->whereNull('services.deleted_at');

		// Role-based filtering
		if ($user->hasRole('serviceProvider') && $user->service_provider) {
			$baseQuery->where('services.service_provider_id', $user->service_provider->id);
		} else {
			$orgIds = $user->organizations_ids;
			$baseQuery->whereExists(function ($q) use ($orgIds) {
				$q->select(DB::raw(1))
					->from('organization_service')
					->whereColumn('organization_service.service_id', 'services.id')
					->whereIn('organization_service.organization_id', $orgIds);
			});
		}

		// Compute stats (before applying status filter)
		$statsRaw = (clone $baseQuery)
			->selectRaw("
				COUNT(*) as total,
				SUM(CASE WHEN services_requests.approved IS NULL AND (services_requests.done IS NULL OR services_requests.done = 0) THEN 1 ELSE 0 END) as pending,
				SUM(CASE WHEN services_requests.done = 1 THEN 1 ELSE 0 END) as completed,
				SUM(CASE WHEN services_requests.approved = 'Yes' THEN 1 ELSE 0 END) as approved,
				SUM(CASE WHEN services_requests.approved = 'No' THEN 1 ELSE 0 END) as rejected
			")
			->first();

		$stats = [
			'total'     => (int) ($statsRaw->total ?? 0),
			'pending'   => (int) ($statsRaw->pending ?? 0),
			'completed' => (int) ($statsRaw->completed ?? 0),
			'approved'  => (int) ($statsRaw->approved ?? 0),
			'rejected'  => (int) ($statsRaw->rejected ?? 0),
		];

		// Apply status filter
		$statusFilter = $request->input('request_status');
		if ($statusFilter) {
			switch ($statusFilter) {
				case 'pending':
					$baseQuery->whereNull('services_requests.approved')
						->where(function ($q) { $q->whereNull('services_requests.done')->orWhere('services_requests.done', 0); });
					break;
				case 'completed':
					$baseQuery->where('services_requests.done', 1);
					break;
				case 'approved':
					$baseQuery->where('services_requests.approved', 'Yes');
					break;
				case 'rejected':
					$baseQuery->where('services_requests.approved', 'No');
					break;
			}
		}

		// Fetch requests
		$rows = $baseQuery->select([
				'services_requests.id',
				"services.{$nameCol} as service_name",
				"services.{$descCol} as service_description",
				'services_requests.date_time',
				'services_requests.comment',
				'services.img_url as service_img',
				'services.price',
				'services.currency',
				'services.hide_price',
				'services_requests.approved',
				'services_requests.done',
				'services_requests.rate',
				'services_requests.review_title',
				'services_requests.review_body',
				'services_requests.reviewed_at',
				'service_providers.id as provider_id',
				'service_providers.company_name as provider_name',
				'service_providers.img_url as provider_img',
				'users.id as founder_user_id',
				DB::raw("CONCAT(users.first_name_en, ' ', users.last_name_en) as founder_name"),
				'users.img_url as founder_img',
				"companies.{$nameCol} as startup_name",
				'companies.id as startup_id',
			])
			->orderBy('services_requests.created_at', 'desc')
			->get();

		// Map to the structure the blade expects
		$requests = $rows->map(function ($row) {
			// Determine display status
			if ($row->done == 1) {
				$status = 'Completed';
			} elseif ($row->approved === 'Yes') {
				$status = 'Approved';
			} elseif ($row->approved === 'No') {
				$status = 'Rejected';
			} else {
				$status = 'Pending';
			}

			return [
				'id'                  => $row->id,
				'title'               => $row->service_name,
				'date'                => $row->date_time ? \Carbon\Carbon::parse($row->date_time)->format('M d, Y • H:i') : '—',
				'description'         => $row->comment ?? '—',
				'service_description' => $row->service_description ?? '—',
				'image_url'           => $row->service_img ? '/191014/' . $row->service_img : '/images/deck-video.svg',
				'status'              => $status,
				'price'               => !$row->hide_price && $row->price > 0 ? $row->price : null,
				'currency'            => $row->currency,
				'rating'              => $row->rate,
				'review_title'        => $row->review_title,
				'review_body'         => $row->review_body,
				'reviewed_at'         => $row->reviewed_at ? \Carbon\Carbon::parse($row->reviewed_at)->format('M d, Y') : null,
				'has_review'          => !is_null($row->rate),
				'founder_img'         => $row->founder_img ? '/191014/' . $row->founder_img : '/images/deck-video.svg',
				'founder_name'        => $row->founder_name ?? 'N/A',
				'founder_url'         => $row->founder_user_id ? route('mentors.profile_view', $row->founder_user_id) : '#',
				'startup_name'        => $row->startup_name ?? 'N/A',
				'startup_url'         => $row->startup_id ? route('company.profile_view', $row->startup_id) : '#',
				'service_provider'    => [
					'id'          => $row->provider_id,
					'name'        => $row->provider_name ?? '—',
					'image_url'   => $row->provider_img ? '/191014/' . $row->provider_img : '/images/deck-video.svg',
					'profile_url' => $row->provider_id ? route('service_provider.profile', $row->provider_id) : '#',
				],
			];
		});

		// Paginate (9 per page)
		$page = $request->get('page', 1);
		$perPage = 9;
		$total = $requests->count();
		$requests = new LengthAwarePaginator(
			$requests->forPage($page, $perPage)->values(),
			$total,
			$perPage,
			$page,
			[
				'path'  => LengthAwarePaginator::resolveCurrentPath(),
				'query' => $request->query(),
			]
		);

		return view('admin.settings.services.requests', compact('stats', 'requests'));
	}

	/**
	 * AJAX: Return the status timeline for a single service request.
	 */
	public function timeline(int $id)
	{
		$user = auth()->user();
		$locale = app()->getLocale();

		// Build base query with authorization scoping
		$requestQuery = DB::table('services_requests')
			->join('services', 'services_requests.service_id', '=', 'services.id')
			->where('services_requests.id', $id)
			->whereNull('services_requests.deleted_at')
			->whereNull('services.deleted_at');

		// Role-based access
		if ($user->hasRole('registered')) {
			$requestQuery->where('services_requests.user_id', $user->id);
		} elseif ($user->hasRole('serviceProvider') && $user->service_provider) {
			$requestQuery->where('services.service_provider_id', $user->service_provider->id);
		} else {
			$orgIds = $user->organizations_ids;
			$requestQuery->whereExists(function ($q) use ($orgIds) {
				$q->select(DB::raw(1))
					->from('organization_service')
					->whereColumn('organization_service.service_id', 'services.id')
					->whereIn('organization_service.organization_id', $orgIds);
			});
		}

		$exists = $requestQuery->select('services_requests.id')->first();
		if (!$exists) {
			return response()->json([
				'status'  => false,
				'message' => __('admin.Service request not found'),
			], 404);
		}

		$nameFirst = "first_name_{$locale}";
		$nameLast  = "last_name_{$locale}";

		$logs = DB::table('service_request_status_logs as l')
			->leftJoin('users as u', 'l.causer_id', '=', 'u.id')
			->where('l.service_request_id', $id)
			->orderBy('l.occurred_at', 'desc')
			->select([
				'l.status',
				'l.comment',
				'l.occurred_at',
				DB::raw("CONCAT(COALESCE(u.{$nameFirst}, u.first_name_en), ' ', COALESCE(u.{$nameLast}, u.last_name_en)) as causer_name"),
			])
			->get();

		$iconMap = [
			'pending'   => 'ti-check',
			'approved'  => 'ti-thumb-up',
			'rejected'  => 'ti-x',
			'completed' => 'ti-circle-check',
		];

		$colorMap = [
			'pending'   => 'success',
			'approved'  => 'primary',
			'rejected'  => 'danger',
			'completed' => 'info',
		];

		$timeline = $logs->map(function ($log) use ($iconMap, $colorMap) {
			return [
				'status'      => $log->status,
				'label'       => __('admin.timeline_status_' . $log->status),
				'description' => $log->comment ?: __('admin.timeline_desc_' . $log->status),
				'causer_name' => trim($log->causer_name) ?: null,
				'occurred_at' => \Carbon\Carbon::parse($log->occurred_at)->format('M d, H:i'),
				'icon'        => $iconMap[$log->status] ?? 'ti-info-circle',
				'color'       => $colorMap[$log->status] ?? 'info',
			];
		});

		return response()->json([
			'status'   => true,
			'timeline' => $timeline,
		]);
	}

	public function oldrequests()
	{
		$user = auth()->user();
		$received = ServicesRequests::whereNotNull('approved')->when($user->hasRole('serviceProvider') && $user->service_provider, function ($q) use ($user) {
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
			$serviceRequest = $serviceRequest->when($user->hasRole('serviceProvider') && $user->service_provider, function ($q) use ($user) {
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
			$serviceRequest->decided_at = now();
			$serviceRequest->save();

			ServiceRequestStatusLogger::log(
				$serviceRequest->id,
				$stat == 'approve' ? 'approved' : 'rejected',
				$user->id,
				$request->input('comment', null)
			);

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
		$user = auth()->user();
		$orgIds = $user->organizations_ids;

		$t = $request->input('type');
		$find = $request->input('find');
		$services = Service::with(['serviceCategory', 'organizations'])
			->where('status', true)
			->when($user->hasRole('serviceProvider') && $user->service_provider, function ($query) use ($user) {
				$provider_id = $user?->service_provider?->id ?? 0;
				$query->where('service_provider_id', $provider_id);
			})
			->whereHas('organizations', function ($q) use ($orgIds) {
				$q->whereIn('organizations.id', $orgIds);
			})
		->orderBy('created_at', 'desc')
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
			$service = DB::table('services')
				->leftJoin('service_providers', 'services.service_provider_id', '=', 'service_providers.id')
				->leftJoin('users as provider_user', 'service_providers.user_id', '=', 'provider_user.id')
				->where('services.id', $id)
				->where('services.status', true)
				->whereNull('services.deleted_at')
				->select([
					'services.*',
					'service_providers.company_name as provider_company',
					'service_providers.contact_name as provider_contact',
					'service_providers.img_url as provider_img',
					'provider_user.id as provider_user_id',
				])
				->first();

			if( empty( $service ) ) { return response()->json(['status' => false, 'title' => 'Not Found', 'message' => __('admin.Service not found')]); }

			// Rating & reviews count via query builder
			$ratingData = DB::table('services_requests')
				->where('service_id', $id)
				->whereNotNull('rate')
				->whereNull('deleted_at')
				->selectRaw('COALESCE(AVG(rate), 0) as avg_rating, COUNT(*) as review_count')
				->first();

			$service->avg_rating   = round($ratingData->avg_rating ?? 0, 1);
			$service->review_count = $ratingData->review_count ?? 0;

			// User's own rating for this service
			$myRate = DB::table('services_requests')
				->where('service_id', $id)
				->where('user_id', Auth::id())
				->whereNotNull('rate')
				->whereNull('deleted_at')
				->value('rate');
			$service->rate = (int) ($myRate ?? 0);

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

			$wasDone = $serviceRequest->done;
			ServicesRequests::updateOrInsert(['id' => $id], ['rate' => $rate, 'rate_comment' => $rate_comment, 'done' => $done]);

			if ($done == 1 && $wasDone != 1) {
				ServiceRequestStatusLogger::log(
					$id,
					'completed',
					auth()->id(),
					$rate_comment
				);
			}

			return redirect()->back()->with(['class' => 'success', 'msg' => __('admin.Service request rated, thank you for your feedback')]);
		}
		return redirect()->back()->with(['class' => 'danger', 'msg' => __('admin.An unexpected error occurred, please try again later')]);
	}

	public function submitReview(SubmitReviewRequest $request)
	{
		$userId    = Auth::id();
		$requestId = $request->input('request_id');

		$serviceRequest = DB::table('services_requests')
			->where('id', $requestId)
			->whereNull('deleted_at')
			->first();

		if (!$serviceRequest) {
			return response()->json([
				'status'  => false,
				'message' => __('admin.Service request not found'),
			], 404);
		}

		if ((int) $serviceRequest->user_id !== $userId) {
			return response()->json([
				'status'  => false,
				'message' => __('admin.unauthorized_action'),
			], 403);
		}

		if ((int) $serviceRequest->done !== 1) {
			return response()->json([
				'status'  => false,
				'message' => __('admin.request_not_completed'),
			], 422);
		}

		DB::table('services_requests')
			->where('id', $requestId)
			->update([
				'rate'         => $request->input('rating'),
				'review_title' => $request->input('review_title'),
				'review_body'  => $request->input('review_body'),
				'reviewed_at'  => now(),
				'updated_at'   => now(),
			]);

		$serviceId = $serviceRequest->service_id;

		$avgData = DB::table('services_requests')
			->where('service_id', $serviceId)
			->whereNotNull('rate')
			->whereNull('deleted_at')
			->selectRaw('COALESCE(AVG(rate), 0) as avg_rating')
			->first();

		DB::table('services')
			->where('id', $serviceId)
			->update([
				'avg_rating'  => round($avgData->avg_rating ?? 0, 2),
				'updated_at'  => now(),
			]);

		// Send notification to service provider
		try {
			$service = DB::table('services')->where('id', $serviceId)->first();

			if ($service && $service->service_provider_id) {
				$provider = DB::table('service_providers')
					->where('id', $service->service_provider_id)
					->first();

				if ($provider && $provider->user_id) {
					$providerUser = User::find($provider->user_id);
					$founder      = User::find($userId);
					$providerLang = $provider->preferred_language ?? 'en';

					$founderName = trim(($founder->first_name_en ?? '') . ' ' . ($founder->last_name_en ?? ''));
					$serviceName = $providerLang === 'ar'
						? ($service->name_ar ?: $service->name_en)
						: $service->name_en;

					$params = [
						'founder_name' => $founderName,
						'service_name' => $serviceName,
						'contact_name' => $provider->contact_name ?? '',
						'rating'       => $request->input('rating'),
						'timestamp'    => now()->format('Y-m-d H:i'),
					];

					$body = __('admin.review_notification_body', $params, $providerLang);

					if ($request->input('review_title')) {
						$body .= __('admin.review_notification_title_line', ['title' => $request->input('review_title')], $providerLang);
					}
					if ($request->input('review_body')) {
						$body .= __('admin.review_notification_review_line', ['review' => $request->input('review_body')], $providerLang);
					}

					$notificationUrl = '/dashboard/requests/all?open_request=' . $requestId . '&tab=review';

					$notification = new TemplateEmail([
						__('admin.new_review_email_subject', $params, $providerLang),
						__('admin.review_notification_greeting', $params, $providerLang),
						$body,
						$notificationUrl,
						__('admin.you_have_new_review', [], $providerLang),
					]);
					$notification->ActionText = __('admin.review_notification_cta', [], $providerLang);

					$providerUser->notify($notification);
				}
			}
		} catch (\Throwable $th) {
			Log::error('Failed to send review notification', [
				'error'      => $th->getMessage(),
				'request_id' => $requestId,
			]);
		}

		return response()->json([
			'status'  => true,
			'message' => __('admin.review_submitted_successfully'),
		]);
	}

	public function book_service(BookServiceRequest $request)
	{
		$user    = auth()->user();
		$service = Service::find($request->id);

		if( empty( $service ) ) { return response()->json(['state' => false, 'title' => 'Not Found', 'message' => __('admin.Service not found')]); }

		$serviceRequest = ServicesRequests::create([
			'service_id' => $service->id,
			'user_id'    => $user->id,
			'comment'    => $request->input('comment'),
			'date_time'  => $request->datetime,
		]);

		ServiceRequestStatusLogger::log(
			$serviceRequest->id,
			'pending',
			$user->id,
			$request->input('comment')
		);

		event(new WebMessage('NewServ', $user->first_name_en . ' ' . $user->last_name_en));

		if( $service->vendor_email && !in_array(env('APP_ENV', 'local'), ['local']) )
		{
			Mail::to($service->vendor_email)->queue(new ServiceRequestNotification($serviceRequest));
		}

		return response()->json(['state' => true, 'title' => 'Success', 'message' => __('admin.service_request_submitted_successfully')]);
	}

	public function myRequests(Request $request)
	{
		$userId = Auth::id();
		$locale = app()->getLocale();
		$nameCol = 'name_' . $locale;
		$descCol = 'description_' . $locale;

		// Base query for the founder's own requests
		$baseQuery = DB::table('services_requests')
			->join('services', 'services_requests.service_id', '=', 'services.id')
			->leftJoin('service_providers', 'services.service_provider_id', '=', 'service_providers.id')
			->join('users', 'services_requests.user_id', '=', 'users.id')
			->leftJoin('companies', 'users.company_id', '=', 'companies.id')
			->where('services_requests.user_id', $userId)
			->whereNull('services_requests.deleted_at')
			->whereNull('services.deleted_at');

		// Compute stats (before applying status filter)
		$statsRaw = (clone $baseQuery)
			->selectRaw("
				COUNT(*) as total,
				SUM(CASE WHEN services_requests.approved IS NULL AND (services_requests.done IS NULL OR services_requests.done = 0) THEN 1 ELSE 0 END) as pending,
				SUM(CASE WHEN services_requests.done = 1 THEN 1 ELSE 0 END) as completed,
				SUM(CASE WHEN services_requests.approved = 'Yes' THEN 1 ELSE 0 END) as approved,
				SUM(CASE WHEN services_requests.approved = 'No' THEN 1 ELSE 0 END) as rejected
			")
			->first();

		$stats = [
			'total'     => (int) ($statsRaw->total ?? 0),
			'pending'   => (int) ($statsRaw->pending ?? 0),
			'completed' => (int) ($statsRaw->completed ?? 0),
			'approved'  => (int) ($statsRaw->approved ?? 0),
			'rejected'  => (int) ($statsRaw->rejected ?? 0),
		];

		// Apply status filter
		$statusFilter = $request->input('request_status');
		if ($statusFilter) {
			switch ($statusFilter) {
				case 'pending':
					$baseQuery->whereNull('services_requests.approved')
						->where(function ($q) { $q->whereNull('services_requests.done')->orWhere('services_requests.done', 0); });
					break;
				case 'completed':
					$baseQuery->where('services_requests.done', 1);
					break;
				case 'approved':
					$baseQuery->where('services_requests.approved', 'Yes');
					break;
				case 'rejected':
					$baseQuery->where('services_requests.approved', 'No');
					break;
			}
		}

		// Fetch requests
		$rows = $baseQuery->select([
				'services_requests.id',
				"services.{$nameCol} as service_name",
				"services.{$descCol} as service_description",
				'services_requests.date_time',
				'services_requests.comment',
				'services.img_url as service_img',
				'services.price',
				'services.currency',
				'services.hide_price',
				'services.hide_vendor_info',
				'services_requests.approved',
				'services_requests.done',
				'services_requests.rate',
				'services_requests.review_title',
				'services_requests.review_body',
				'services_requests.reviewed_at',
				'service_providers.id as provider_id',
				'service_providers.company_name as provider_name',
				'service_providers.img_url as provider_img',
				'service_providers.user_id as provider_user_id',
				'users.id as founder_user_id',
				DB::raw("CONCAT(users.first_name_en, ' ', users.last_name_en) as founder_name"),
				'users.img_url as founder_img',
				"companies.{$nameCol} as startup_name",
				'companies.id as startup_id',
			])
			->orderBy('services_requests.created_at', 'desc')
			->get();

		// Map to the structure the blade expects
		$requests = $rows->map(function ($row) {
			if ($row->done == 1) {
				$status = 'Completed';
			} elseif ($row->approved === 'Yes') {
				$status = 'Approved';
			} elseif ($row->approved === 'No') {
				$status = 'Rejected';
			} else {
				$status = 'Pending';
			}

			$hideVendor = (bool) $row->hide_vendor_info;

			return [
				'id'                  => $row->id,
				'title'               => $row->service_name,
				'date'                => $row->date_time ? \Carbon\Carbon::parse($row->date_time)->format('M d, Y • H:i') : '—',
				'description'         => $row->comment ?? '—',
				'service_description' => $row->service_description ?? '—',
				'image_url'           => $row->service_img ? '/191014/' . $row->service_img : '/images/deck-video.svg',
				'status'              => $status,
				'price'               => !$row->hide_price && $row->price > 0 ? $row->price : null,
				'currency'            => $row->currency,
				'hide_vendor_info'    => $hideVendor,
				'rating'              => $row->rate,
				'review_title'        => $row->review_title,
				'review_body'         => $row->review_body,
				'reviewed_at'         => $row->reviewed_at ? \Carbon\Carbon::parse($row->reviewed_at)->format('M d, Y') : null,
				'has_review'          => !is_null($row->rate),
				'founder_name'        => $row->founder_name ?? 'N/A',
				'founder_img'         => $row->founder_img ? '/191014/' . $row->founder_img : '/images/deck-video.svg',
				'founder_url'         => $row->founder_user_id ? route('mentors.profile_view', $row->founder_user_id) : '#',
				'startup_name'        => $row->startup_name ?? 'N/A',
				'startup_url'         => $row->startup_id ? route('company.profile_view', $row->startup_id) : '#',
				'service_provider'    => [
					'id'          => $row->provider_id,
					'name'        => $row->provider_name ?? '—',
					'image_url'   => $row->provider_img ? '/191014/' . $row->provider_img : '/images/deck-video.svg',
					'profile_url' => !$hideVendor && $row->provider_id ? route('service_provider.profile', $row->provider_id) : null,
				],
			];
		});

		// Paginate (9 per page)
		$page = $request->get('page', 1);
		$perPage = 9;
		$total = $requests->count();
		$requests = new LengthAwarePaginator(
			$requests->forPage($page, $perPage)->values(),
			$total,
			$perPage,
			$page,
			[
				'path'  => LengthAwarePaginator::resolveCurrentPath(),
				'query' => $request->query(),
			]
		);

		return view('admin.clients.myrequests', compact('stats', 'requests'));
	}
}
