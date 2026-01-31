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
use App\Services\ServiceProviderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use stdClass;

class serviceProviderController extends Controller
{
    protected $serviceProviderService;

    public function __construct(ServiceProviderService $serviceProviderService)
    {
        $this->serviceProviderService = $serviceProviderService;
    }

	public function storeStep1(StoreServiceProviderStep1Request $request)
	{
		try {
			$this->serviceProviderService->updateStep1(
				auth()->user(), 
				$request->validated(), 
				$request->file('company_logo')
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
		$logo = setting('Logo', 'images/background/logo.png');
		$termsUrl = setting('terms_url', '#') ;
		$privacyUrl = setting('privacy_url', '#');
		// if( trim( $termsUrl ) == '#' ) { $termsUrl = null; }
		// if( trim( $privacyUrl ) == '#' ) { $privacyUrl = null; }
		return view('auth.provider_register', compact('setting', 'logo', 'termsUrl', 'privacyUrl'));
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

		/* $upload_file = [];
		if( $request->hasFile('img_url') )
		{
			$upload_file = uploader( 'serviceProvider', $request->file('img_url'), '', true );
		} */

		$contact_name = $request->contact_name;
		$registration = new serviceProviderRegistration; 
		$registration->company_name = $request->company_name;
		$registration->contact_name = $request->contact_name;
		$registration->email = $request->email;
		$registration->country_code = $request->country_code;
		$registration->phone = '+' . $request->country_code . $request->phone;
		$registration->status = null;
		$registration->user_id = null;
		$registration->ip_address = $request->ip();
		$registration->save();

		// send confirmation email here
		if( !in_array(env('APP_ENV', 'local'), ['local']) )
		{
			if( config('mail.driver') && config('mail.host') && config('mail.port') && config('mail.username') && config('mail.password') )
			{
				Mail::to($registration->email)->queue(new serviceProviderRegistrationMail( $contact_name ));
			}
		}

		return redirect()->back()->with('success', __('auth.provider_registration_recorded'));
	}

	public function requests()
	{
		$l = app()->getLocale();
		Carbon::setLocale($l);
		$requests = DB::table('service_provider_registration AS spr')
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
			->get();

		$requests = $requests->map(function( $request ) use ( $l ) {
			$submitted_at = Carbon::parse($request->created_at);
			$request->submitted_at = $submitted_at->diffForHumans();
			$request->action_since = $request->action_at ? Carbon::parse($request->action_at)->diffForHumans() : '';
			$request->action_by = $request->{'full_name_'. $l} ?? '';
			return $request;
		});
		return view('admin.serviceProviders.requests', compact('requests'));
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
		else
		{
			$login_url = route('login');
			$email = $registration->email;
			$phone = $registration->phone;
			$country_code = $registration->country_code;
			$contact_name = $registration->contact_name;
			$company_name = $registration->company_name;
			if( $request->request_action == 'approve' )
			{
				try {
					$parse_name = explode(' ', $contact_name);
					$first_name = $parse_name[0];
					$last_name = '';
					unset($parse_name[0]);
					$parse_name = array_values($parse_name);
					$last_name = implode(' ', $parse_name);

					DB::beginTransaction();
					$password = Str::password(10, symbols: true);
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
					$provider_user->email_verified_at = date('Y-m-d H:i:s');
					$provider_user->password = Hash::make( $password );
					$provider_user->save();
					$provider_user->allotRole('serviceProvider');

					$registration->status = true;
					$registration->action_by = $user->id;
					$registration->action_at = now();
					$registration->user_id = $provider_user->id;
					$registration->save();

					$provider = new serviceProviders;
					$provider->company_name = $company_name;
					$provider->contact_name = $contact_name;
					$provider->email = $email;
					$provider->phone = $phone;
					$provider->status = true;
					$provider->user_id = $provider_user->id;
					$provider->save();

					DB::commit();
					if( !in_array(env('APP_ENV', 'local'), ['local']) )
					{
						if( config('mail.driver') && config('mail.host') && config('mail.port') && config('mail.username') && config('mail.password') )
						{
							Mail::to( $email )->queue(new serviceProviderApproveMail( $contact_name, $email, $password, $login_url ));
						}
					}
					return redirect()->route('services_providers.requests')->with('success', __('admin.serviceProviderRequestApproved'));
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

				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					if( config('mail.driver') && config('mail.host') && config('mail.port') && config('mail.username') && config('mail.password') )
					{
						Mail::to( $email )->queue(new serviceProviderRejectMail( $contact_name, $reason ));
					}
				}
				return redirect()->route('services_providers.requests')->with('success', __('admin.serviceProviderRequestRejected'));
			}
		}
	}

	public function index()
	{
		$user = auth()->user();
		$l = app()->getLocale();
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
		return view('admin.serviceProviders.index', compact('providers'));
	}
}
