<?php

namespace App\Http\Controllers;

use App\User;
use App\Settings;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\serviceProviders;
use App\serviceProviderRegistration;
use App\Mail\serviceProviderRejectMail;
use App\Mail\serviceProviderApproveMail;
use App\Mail\serviceProviderRegistrationMail;
use App\Http\Requests\StoreServiceProviderStep1Request;
use App\Http\Requests\UpdateServiceProviderStep2Request;
use App\Http\Requests\UpdateServiceProviderStep3Request;
use App\Http\Requests\UpdateServiceProviderStep4Request;
use App\Services\ServiceProviderService;
use App\Country;
use App\State;
use App\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use stdClass;
use App\Services\FileManagerService;

class serviceProviderController extends Controller
{
    protected $serviceProviderService;

    public function __construct(ServiceProviderService $serviceProviderService)
    {
        $this->serviceProviderService = $serviceProviderService;
    }

    /**
     * Generate a password that satisfies the platform validation:
     * at least 1 uppercase, 1 lowercase, 1 digit, 1 special character, 8-20 chars.
     */
    private function generateCompliantPassword(int $length = 12): string
    {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghjkmnpqrstuvwxyz';
        $digits  = '23456789';
        $symbols = '!@#$%&*?';

        // Guarantee one of each required type
        $password  = $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        // Fill remaining length from the combined pool
        $pool = $upper . $lower . $digits . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $pool[random_int(0, strlen($pool) - 1)];
        }

        // Shuffle to avoid predictable positions
        return str_shuffle($password);
    }

	public function storeStep1(StoreServiceProviderStep1Request $request)
	{
		try {
			$provider = null;
			if ($request->filled('service_provider_id')) {
				if (!auth()->user()->ableTo('services_providers.edit')) {
					abort(403);
				}
				$provider = serviceProviders::findOrFail($request->service_provider_id);
			}

			$this->serviceProviderService->updateStep1(
				auth()->user(),
				$request->validated(),
				$request->file('company_logo'),
				$provider
			);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('admin.ProfileSaved')]);
            }

			return redirect()->back()->with('success', __('admin.ProfileSaved'));
		} catch (\Exception $e) {
			Log::error('Store Step 1 Error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('admin.An unexpected error occurred, please try again later')], 500);
            }

			return redirect()->back()->with('error', __('admin.An unexpected error occurred, please try again later'));
		}
	}

	public function storeStep2(UpdateServiceProviderStep2Request $request)
	{
		try {
			$provider = null;
			if ($request->filled('service_provider_id')) {
				if (!auth()->user()->ableTo('services_providers.edit')) {
					abort(403);
				}
				$provider = serviceProviders::findOrFail($request->service_provider_id);
			}

			$this->serviceProviderService->updateStep2(
				auth()->user(),
				$request->validated(),
				$request->file('hero_image'),
				$provider
			);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('admin.ProfileSaved')]);
            }

			return redirect()->back()->with('success', __('admin.ProfileSaved'));
		} catch (\Exception $e) {
			Log::error('Store Step 2 Error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('admin.An unexpected error occurred, please try again later')], 500);
            }

			return redirect()->back()->with('error', __('admin.An unexpected error occurred, please try again later'));
		}
	}

	public function storeStep3(UpdateServiceProviderStep3Request $request)
	{
		try {
			$provider = null;
			if ($request->filled('service_provider_id')) {
				if (!auth()->user()->ableTo('services_providers.edit')) {
					abort(403);
				}
				$provider = serviceProviders::findOrFail($request->service_provider_id);
			}

			$this->serviceProviderService->updateStep3(
				auth()->user(),
				$request->validated(),
				$provider
			);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('admin.ProfileSaved')]);
            }

			return redirect()->back()->with('success', __('admin.ProfileSaved'));
		} catch (\Exception $e) {
			Log::error('Store Step 3 Error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('admin.An unexpected error occurred, please try again later')], 500);
            }

			return redirect()->back()->with('error', __('admin.An unexpected error occurred, please try again later'));
		}
	}

    public function storeStep4(UpdateServiceProviderStep4Request $request)
    {
        try {
            $provider = null;
            if ($request->filled('service_provider_id')) {
                if (!auth()->user()->ableTo('services_providers.edit')) {
                    abort(403);
                }
                $provider = serviceProviders::findOrFail($request->service_provider_id);
            }

            $this->serviceProviderService->updateStep4(
                auth()->user(),
                $request->validated(),
                $provider
            );

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('admin.ProfileSaved')]);
            }

            return redirect()->back()->with('success', __('admin.ProfileSaved'));
        } catch (\Exception $e) {
            Log::error('Store Step 4 Error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('admin.An unexpected error occurred, please try again later')], 500);
            }

            return redirect()->back()->with('error', __('admin.An unexpected error occurred, please try again later'));
        }
    }

	public function get_media( $path = null )
	{
		try {
			$instance = Storage::disk('serviceProvider');
			if( $instance->directoryExists( $path ) ) { return response()->view('errors.404')->setStatusCode( 404 ); }
			$file = $instance->response( $path );
			$mimeType = $instance->mimeType($path);
			if( $mimeType == 'image/svg' ) { $mimeType = 'image/svg+xml'; }
			$file->headers->set('Content-Type', $mimeType);
			return $file;
		} catch( \Throwable $th ) { return response()->view('errors.404')->setStatusCode( 404 ); }
	}

	public function registration()
	{
		$setting = Settings::first();
		// setting('Logo') returns a BARE FILE NAME with no images/logo/ prefix and no
		// asset() wrapping, so it never resolved to a usable URL. brandLogo() builds
		// the real URL and falls back when the configured file is missing from disk.
		$logo = brandLogo();
		$platformName = brandName();
		$termsUrl = setting('terms_url', '#') ;
		$privacyUrl = setting('privacy_url', '#');
		// if( trim( $termsUrl ) == '#' ) { $termsUrl = null; }
		// if( trim( $privacyUrl ) == '#' ) { $privacyUrl = null; }
		return view('auth.provider_register', compact('setting', 'logo', 'platformName', 'termsUrl', 'privacyUrl'));
	}

	public function registration_request(Request $request)
	{
		$request->validate([
			'company_name' => 'required',
			'contact_name' => 'required',
			'email' => 'required|email:rfc,dns|unique:users,email|unique:service_provider_registration,email',
			'country_code' => 'required|digits_between:1,3',
			'phone' => [
				'required',
				'digits_between:4,12',
				function( $attribute, $value, $fail ) use ( $request ) {
					$combined = '+' . $request->country_code . $value;
					$exists = DB::table('service_provider_registration')
						->where('phone', $combined)
						->first();

					if( !empty( $exists ) ) { $fail( __('auth.validate_phone_unique')); }
					elseif( !preg_match('/^\+[1-9]\d{1,14}$/', $combined) ) { $fail( __('auth.validate_phone_format')); }
				},
			],
			'terms_agreement' => 'required|in:agreed',
		], [
			'company_name.required' => __('auth.validate_company_name_required'),
			'contact_name.required' => __('auth.validate_contact_name_required'),
			'email.required' => __('auth.validate_email_required'),
			'email.email' => __('auth.validate_email_email'),
			'email.unique' => __('auth.validate_email_unique'),
			'country_code.required' => __('auth.validate_country_code_required'),
			'country_code.digits_between' => __('auth.validate_country_code_digits_between'),
			'phone.required' => __('auth.validate_phone_required'),
			'phone.digits_between' => __('auth.validate_phone_digits_between'),
			'terms_agreement.required' => __('auth.validate_terms_agreement_required'),
			'terms_agreement.in' => __('auth.validate_terms_agreement_in'),
		]);

		$contact_name = $request->contact_name;
		$email = $request->email;
		$phone = '+' . $request->country_code . $request->phone;
		$company_name = $request->company_name;
		$country_code = $request->country_code;
		$locale = app()->getLocale();

		try {
			DB::beginTransaction();

			$registration = new serviceProviderRegistration;
			$registration->company_name = $company_name;
			$registration->contact_name = $contact_name;
			$registration->email = $email;
			$registration->country_code = $country_code;
			$registration->phone = $phone;
			$registration->status = null;
			$registration->ip_address = $request->ip();
			$registration->locale = $locale;
			$registration->save();

			$parse_name = explode(' ', $contact_name);
			$first_name = $parse_name[0];
			unset($parse_name[0]);
			$last_name = implode(' ', array_values($parse_name));

			$password = $this->generateCompliantPassword();
			$provider_user = new User;
			$provider_user->first_name_en = $first_name;
			$provider_user->last_name_en = $last_name;
			$provider_user->first_name_ar = $first_name;
			$provider_user->last_name_ar = $last_name;
			$provider_user->email = $email;
			$provider_user->phone = $phone;
			$provider_user->country_code = $country_code;
			$provider_user->email_verified_at = now();
			$provider_user->password = Hash::make($password);
			$provider_user->save();
			$provider_user->allotRole('serviceProvider');

			$provider = new serviceProviders;
			$provider->company_name = $company_name;
			$provider->contact_name = $contact_name;
			$provider->email = $email;
			$provider->phone = $phone;
			$provider->status = false;
			$provider->user_id = $provider_user->id;
			$provider->save();

			$registration->user_id = $provider_user->id;
			$registration->save();

			DB::commit();

			$login_url = route('login');
			if( in_array(env('APP_ENV', 'local'), ['local']) )
			{
				return view('emails.service-providers.registration', compact('contact_name', 'email', 'password', 'login_url', 'locale'));
			}

			if( config('mail.driver') && config('mail.host') && config('mail.port') && config('mail.username') && config('mail.password') )
			{
				Mail::to($email)->queue(new serviceProviderRegistrationMail($contact_name, $email, $password, $login_url, $locale));
			}

			return redirect()->back()->with('success', __('auth.provider_registration_recorded'));
		} catch (\Exception $e) {
			DB::rollBack();
			return redirect()->back()->with('error', $e->getMessage());
		} catch (\Throwable $e) {
			DB::rollBack();
			return redirect()->back()->with('error', $e->getMessage());
		}
	}

	public function requests()
	{
		return redirect()->route('services_providers.index');
	}

	public function takeAction(Request $request)
	{
		$user = auth()->user();
		$request->validate([
			'request_id' => 'required|exists:service_provider_registration,id',
			'request_action' => 'required|in:approve,reject',
			'request_reason' => 'required_if:request_action,reject',
		]);

		$registration = serviceProviderRegistration::whereNull('status')->find( $request->request_id );
		if( !$registration ) { return redirect()->back()->with('error', __('admin.serviceProviderRequestNotFound')); }

		$login_url = route('login');
		$email = $registration->email;
		$phone = $registration->phone;
		$country_code = $registration->country_code;
		$contact_name = $registration->contact_name;
		$company_name = $registration->company_name;
		$locale = $registration->locale ?? 'en';

		if( $request->request_action == 'approve' )
		{
			try {
				DB::beginTransaction();

				if( $registration->user_id )
				{
					// New flow: user already exists from registration (password was set at registration time)
					$provider_user = User::find($registration->user_id);
					$provider_user->email_verified_at = now();
					$provider_user->save();

					DB::table('service_providers')
						->where('user_id', $provider_user->id)
						->update(['status' => true]);
				}
				else
				{
					// Legacy flow: create user and service_providers record
					$password = $this->generateCompliantPassword();

					$parse_name = explode(' ', $contact_name);
					$first_name = $parse_name[0];
					unset($parse_name[0]);
					$last_name = implode(' ', array_values($parse_name));

					$provider_user = User::where('email', $email)->first();
					if($provider_user == null){
						$provider_user = new User;
					}
					$provider_user->first_name_en = $first_name;
					$provider_user->last_name_en = $last_name;
					$provider_user->first_name_ar = $first_name;
					$provider_user->last_name_ar = $last_name;
					$provider_user->email = $email;
					$provider_user->phone = $phone;
					$provider_user->country_code = $country_code;
					$provider_user->email_verified_at = now();
					$provider_user->password = Hash::make($password);
					$provider_user->save();
					$provider_user->allotRole('serviceProvider');

					$provider = new serviceProviders;
					$provider->company_name = $company_name;
					$provider->contact_name = $contact_name;
					$provider->email = $email;
					$provider->phone = $phone;
					$provider->status = true;
					$provider->user_id = $provider_user->id;
					$provider->save();
				}

				$registration->status = true;
				$registration->action_by = $user->id;
				$registration->action_at = now();
				$registration->user_id = $provider_user->id;
				$registration->save();

				DB::commit();
				if( in_array(env('APP_ENV', 'local'), ['local']) )
				{
					return view('emails.service-providers.approve', compact('contact_name', 'login_url', 'locale'));
				}

				if( config('mail.driver') && config('mail.host') && config('mail.port') && config('mail.username') && config('mail.password') )
				{
					Mail::to( $email )->queue(new serviceProviderApproveMail( $contact_name, $login_url, $locale ));
				}
				return redirect()
						->route('services_providers.index')
						->with('success', __('admin.serviceProviderRequestApproved'));
			}
			catch (\Exception $e) {
				DB::rollBack();
				return redirect()->back()->with('error', $e->getMessage());
			}
			catch (\Throwable $e) {
				DB::rollBack();
				return redirect()->back()->with('error', $e->getMessage());
			}
		}
		else
		{
			$reason = $request->request_reason;

			$registration->status = false;
			$registration->action_by = $user->id;
			$registration->action_at = now();
			$registration->reason = $reason;
			$registration->save();

			if( in_array(env('APP_ENV', 'local'), ['local']) )
			{
				return view('emails.service-providers.reject', compact('contact_name', 'reason', 'locale'));
			}

			if( config('mail.driver') && config('mail.host') && config('mail.port') && config('mail.username') && config('mail.password') )
			{
				Mail::to( $email )->queue(new serviceProviderRejectMail( $contact_name, $reason, $locale ));
			}
			return redirect()->route('services_providers.index')->with('success', __('admin.serviceProviderRequestRejected'));
		}
	}

	public function editProfile($id)
	{
		$user = auth()->user();
		if (!$user->ableTo('services_providers.edit')) {
			abort(403);
		}

		$provider = serviceProviders::findOrFail($id);
		$client = $provider->user;

		$countries = Country::all();
		$states = $provider->country_id
			? State::where('country_id', $provider->country_id)->get()
			: collect();
		$cities = $provider->state_id
			? City::where('state_id', $provider->state_id)->get()
			: collect();

		return view('admin.serviceProviders.editProfile', compact(
			'client', 'provider', 'countries', 'states', 'cities'
		));
	}

	public function index()
	{
		$user = auth()->user();
		$l = app()->getLocale();
		Carbon::setLocale($l);

		// ─── Enrolled Service Providers ─────────────────────────────
		$providers = DB::table('service_providers AS sp')
			->select([
				'sp.id',
				'sp.company_name',
				'sp.contact_name',
				'sp.email',
				'sp.phone',
				'sp.status',
				'sp.user_id',
            	DB::raw("CONCAT(u.first_name_en, ' ', u.last_name_en) as user_name_en"),
            	DB::raw("CONCAT(u.first_name_ar, ' ', u.last_name_ar) as user_name_ar"),
				'u.phone AS user_phone',
				'u.email AS user_email',
				'u.img_url AS user_img_url',
			])
			->leftJoin('users as u', 'u.id', '=', 'sp.user_id')
		->get();

		$providers = $providers->map(function( $provider ) use ( $l ) {
			$_user = new stdClass;
			$output = new stdClass;
			$output->id = $provider->id;
    		$output->company_name = $provider->company_name;
			$output->contact_name = $provider->contact_name;
			$output->email = $provider->email;
			$output->phone = $provider->phone;
			$output->status = $provider->status;

			$_user->id = $provider->user_id;
			$_user->name_en = $provider->user_name_en;
			$_user->name_ar = $provider->user_name_ar;
			$_user->phone = $provider->user_phone;
			$_user->email = $provider->user_email;
			$_user->img_url = User::image_handler( $provider->user_img_url );
			$output->user = $_user;
			return $output;
		});

		// ─── Registration Requests (all) ────────────────────────────
		$allRequests = DB::table('service_provider_registration AS spr')
			->select([
				'spr.id',
				'spr.company_name',
				'spr.contact_name',
				'spr.email',
				'spr.phone',
				'spr.status',
				'spr.user_id',
				'spr.action_by',
				DB::raw("CONCAT(TRIM(COALESCE(U.first_name_en, '')), IFNULL(CONCAT(' ', TRIM(U.last_name_en)), '')) as full_name_en"),
				DB::raw("CONCAT(TRIM(COALESCE(U.first_name_ar, '')), IFNULL(CONCAT(' ', TRIM(U.last_name_ar)), '')) as full_name_ar"),
				'spr.action_at',
				'spr.reason',
				'spr.ip_address',
				'spr.created_at',
				'spr.updated_at',
			])
			->leftJoin('users as U', 'U.id', '=', 'spr.action_by')
			->orderByDesc('spr.created_at')
			->get();

		$allRequests = $allRequests->map(function( $request ) use ( $l ) {
			$submitted_at = Carbon::parse($request->created_at);
			$request->submitted_at = $submitted_at->diffForHumans();
			$request->action_since = $request->action_at ? Carbon::parse($request->action_at)->diffForHumans() : '';
			$request->action_by = $request->{'full_name_'. $l} ?? '';
			return $request;
		});

		// ─── Split into pending vs history ──────────────────────────
		$pendingRequests = $allRequests->filter(fn($r) => is_null($r->status))->values();
		$pending_requests_count = $pendingRequests->count();

		return view('admin.serviceProviders.index', compact(
			'providers', 'pendingRequests', 'allRequests', 'pending_requests_count'
		));
	}

	// ─── Client CRUD ────────────────────────────────────────────────

	private function authorizeClientAccess($providerId)
	{
		$user = auth()->user();
		$provider = serviceProviders::findOrFail($providerId);

		$isOwner = $provider->user_id === $user->id;
		if (!$isOwner && !$user->ableTo('services_providers.edit')) {
			abort(403);
		}

		return $provider;
	}

	public function storeClient(Request $request, $id)
	{
		$provider = $this->authorizeClientAccess($id);

		$request->validate([
			'client_name' => 'required|string|max:100',
			'client_url' => ['nullable', 'url:http,https', 'max:500', 'regex:/^https?:\/\/([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(\/.*)?$/'],
			'client_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
		]);

		$data = [
			'name' => $request->client_name,
			'external_link' => $request->client_url,
			'order' => $provider->clients()->count(),
		];

		if ($request->hasFile('client_logo')) {
			$data['logo_url'] = app(FileManagerService::class)->upload($request->file('client_logo'), 'service_providers_clients', 'public_uploads', 'image');
		}

		$client = $provider->clients()->create($data);

		return response()->json(['success' => true, 'message' => __('admin.client_added_successfully'), 'client' => $client]);
	}

	public function updateClient(Request $request, $id, $clientId)
	{
		$provider = $this->authorizeClientAccess($id);

		$request->validate([
			'client_name' => 'required|string|max:100',
			'client_url' => ['nullable', 'url:http,https', 'max:500', 'regex:/^https?:\/\/([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(\/.*)?$/'],
			'client_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
		]);

		$client = $provider->clients()->findOrFail($clientId);

		$client->name = $request->client_name;
		$client->external_link = $request->client_url;

		if ($request->hasFile('client_logo')) {
			$client->logo_url = app(FileManagerService::class)->upload($request->file('client_logo'), 'service_providers_clients', 'public_uploads', 'image');
		}

		$client->save();

		return response()->json(['success' => true, 'message' => __('admin.client_updated_successfully'), 'client' => $client]);
	}

	public function destroyClient($id, $clientId)
	{
		$provider = $this->authorizeClientAccess($id);
		$provider->clients()->findOrFail($clientId)->delete();

		return response()->json(['success' => true, 'message' => __('admin.client_deleted_successfully')]);
	}

	public function updateClientsVisibility(Request $request, $id)
	{
		$provider = $this->authorizeClientAccess($id);
		$provider->update(['show_clients' => (bool) $request->show_clients]);

		return response()->json(['success' => true]);
	}
}
