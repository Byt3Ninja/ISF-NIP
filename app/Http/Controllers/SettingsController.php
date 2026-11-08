<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\User;
use App\Cycle;
use App\Event;
use App\State;
use File, URL;
use App\Sector;
use App\Country;
use App\Calendar;
use App\Currency;
use App\Messages;
use App\Settings;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Validation\Rule;
use App\Invitation;
use App\Technology;
use App\ReminderLog;
use App\Organization;
use App\StaticCategory;
use App\MentorshipRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\UpdateSocialLoginRequest;
use App\Http\Requests\UpdatePlatformBrandingRequest;
use App\Notifications\UserInvitationNotification;
use App\Services\EmailSettingsStore;
use App\Services\PlatformBrandingService;
use App\Services\FileManagerService;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['showSetPasswordForm', 'acceptInvitation']);
    }

		public function index()
		{
				$pages = [
						['title' => 'general', 'route' => Auth::user()->hasRole('Admin') ? 'settings.system_general_view' : 'settings.organization_manage'],
						['title' => 'roles_permissions', 'route' => 'roles.index'],
						['title' => 'importer_tool', 'route' => 'importer.index'],
						['title' => 'organization_types', 'route' => 'organization-types.index'],
						['title' => 'currencies', 'route' => 'currencies.index'],
						['title' => 'tags', 'route' => 'Tags.index'],
						['title' => 'categories', 'route' => 'Category.index'],
						['title' => 'subcategories', 'route' => 'subcategories.index'],
						['title' => 'stages', 'route' => 'stages.index'],
						['title' => 'countries', 'route' => 'country-management.index'],
						['title' => 'labels', 'route' => 'Labels.index'],
						['title' => 'queue_jobs', 'route' => 'queue.jobs'],
						['title' => 'industry', 'route' => 'settings.industry_view'],
						['title' => 'expertise', 'route' => 'settings.expertise_view'],
						['title' => 'notification_templates', 'route' => 'notification-templates.index'],
						['title' => 'notification_triggers', 'route' => 'notification-triggers.index'],
						['title' => 'license_key', 'route' => 'license.activate_view_form']
				];
				// GV-10436: the Entity options of ISF's Education Status
				if (isIsf()) {
						array_splice($pages, 4, 0, [['title' => 'education_entities', 'route' => 'education-entities.index']]);
				}

				return view('admin.settings.index', compact('pages'));
		}

	public function get_settings( Request $request )
	{
		$Title = Settings::findOrFail('1');
		$Icons = Settings::where('type','2')->get();
		$Img1 =  Settings::findOrFail('7');
		$Img2 =  Settings::findOrFail('8');
		$Img3 =  Settings::findOrFail('9');
		$Counters = Settings::where('type','6')->get();
		$countries = Country::all();
		$currencies = Currency::where('enabled',1)->get();
		$states= State::all();
		$settings = Settings::find(1);
		$json = File::get(base_path('database/dbFiles/countries.json'));
		$_countries = json_decode($json, true);
		$_countries = collect( $_countries )->map(function ($country) {
			$country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
			return $country;
		});
		$countries = $countries->map(function ( $country ) use ( $_countries ) {
			$getCountry = $_countries->where('iso3', $country->iso3)->first();
			$flag = null; $emoji = null;
			if( !empty( $getCountry ) ) { $flag = $getCountry['flag']; $emoji = $getCountry['emoji']; }
			$country->flag = $flag;
			$country->emoji = $emoji;
			return $country;
		});
		return view('admin.settings.editegeneral')
				->with('Title',$Title)->with('Icons',$Icons)->with('Img1',$Img1)
				->with('Img2',$Img2)->with('Img3',$Img3)->with('Counters',$Counters)
				->with('countries', $countries)->with('states', $states)->with('currencies',$currencies)
				->with('settings', $settings)
				->with('emailConfig', EmailSettingsStore::platform()->viewData())
				// Re-open the tab that was just saved (or failed validation) instead of always "General".
				->with('activeTab', session('settings_tab', old('settings_tab', 'general')));
	}

	public function update_settings(Request $request)
	{
		$Title = Settings::findOrfail('1');
		$Icon1 = Settings::findOrfail('2');
		$Icon2 = Settings::findOrfail('3');
		$Icon3 = Settings::findOrfail('4');
		$Icon4 = Settings::findOrfail('5');
		$Icon5 = Settings::findOrfail('6');

		$Icon1->url = $request->input('facebook');
		$Icon1->Save();
		$Icon2->url = $request->input('twitter');
		$Icon2->Save();
		$Icon3->url = $request->input('youtube');
		$Icon3->Save();
		$Icon4->url = $request->input('instagram');
		$Icon4->Save();
		$Icon5->url = $request->input('linkedin');
		$Icon5->Save();
		/// Img 1
		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

	/** H1: the platform About / Contact pages are global settings rows, editable by admins only. */
	private function authorizeAboutContactPages(): void
	{
		abort_unless(auth()->user()->hasRole('admin') && auth()->user()->ableTo('settings.system_general_update'), 403);
	}

	public function get_aboutus(Request $request)
	{
		$this->authorizeAboutContactPages();
		$allabout = Settings::findorfail('10');
		return view('admin.settings.editaboutus')->with('allabout',$allabout);
	}

	public function update_aboutus(Request $request)
	{
		$this->authorizeAboutContactPages();
		$allabout = Settings::findorfail('10');
		$allabout->title = $request->input('title');
		$allabout->ar_title = $request->input('ar_title');
		$allabout->dsc = $request->input('dsc');
		$allabout->ar_dsc = $request->input('ar_dsc');
		$allabout->set1 = $request->input('set1');
		$allabout->ar_set1 = $request->input('ar_set1');
		$allabout->set2 = $request->input('set2');
		$allabout->ar_set2 = $request->input('ar_set2');
		$allabout->url = $request->input('url');
		$allabout->Save();
		return redirect('dashboard/setaboutus')->with('message', 'StoredSuccessfully');
	}

	public function get_contactus(Request $request)
	{
		$this->authorizeAboutContactPages();
		$allcontact = Settings::findorfail('11');
		return view('admin.settings.editcontactus')->with('allcontact',$allcontact);
	}

	public function update_contactus(Request $request)
	{
		$this->authorizeAboutContactPages();
		$allcontact = Settings::findorfail('11');
		$allcontact->title = $request->input('title');
		$allcontact->ar_title = $request->input('ar_title');
		$allcontact->dsc = $request->input('dsc');
		$allcontact->ar_dsc = $request->input('ar_dsc');
		$allcontact->set1 = $request->input('set1');
		$allcontact->ar_set1 = $request->input('ar_set1');
		$allcontact->set2 = $request->input('set2');
		$allcontact->ar_set2 = $request->input('ar_set2');
		$allcontact->Save();
		return redirect('dashboard/setcontactus')->with('message', 'StoredSuccessfully');
	}

	//// Contact US Messages
	public function mymsg()
	{
		$getmsgs = messages::orderBy('read')->orderBy('created_at','DESC')->get(['id','name','email','subject','read','internal_id','replyBy']);
		return view('admin.settings.messages')->with('getmsgs',$getmsgs);
	}

	//// Send reply via mail
	public function msgreply(Request $request)
	{
		/// Send Mail
		$Email = $request->remail;
		if ($Email == filter_var($Email, FILTER_VALIDATE_EMAIL))
		{
			$user = new User();
			$user->email = $Email;
			$user->id = 1;
			$rfullname = $request->rname;
			$MSG = new TemplateEmail;
			$MSG->Message = [$request->subject.'- VI portal', "Dear " . $rfullname . ",", $request->message, "/dashboard"];
            if( !in_array(env('APP_ENV', 'local'), ['local']) )
            {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }
			Messages::find($request->mid)->update([
				'reSubject' => $request->subject,
				'reMessage' => $request->message,
				'replyBy' => Auth::user()->id,
			]);
			return redirect()->back()->with(['msg' => 'MessageSent', 'class' => 'success']);
		}
		/// End of send
		return redirect()->back()->with(['msg' => 'MessageError', 'class' => 'danger']);
	}

	public function openonemsg($id)
	{
		$getmsg = messages::findOrFail($id);
		$getmsg->read = 1;
		$getmsg->user_id = Auth::user()->id;
		$getmsg->Save();
		$user=[];
		if ($getmsg->replyBy) {$user = User::where('id',$getmsg->replyBy)->first(['first_name_ar','last_name_ar','first_name_en','last_name_en']);}
		return response()->json([$getmsg,$user]);
	}
	public function deletemsg($id)
	{
		$read = messages::findOrfail($id);
		$read->user_id = Auth::user()->id;
		$read->Save();
		$read->delete();
		return redirect('dashboard/mymsg')->with('message', 'DeletedSuccessfully');
	}

	public function makeread($id)
	{
		$read = messages::findOrfail($id);
		$read->read = 1;
		$read->user_id = Auth::user()->id;
		$read->Save();
		return redirect()->back();
	}
	public function makeunread($id)
	{
		$read = messages::findOrfail($id);
		$read->user_id = Auth::user()->id;
		$read->read = 0;
		$read->Save();
		return redirect()->back();
	}

	public function calendar(Request $request)
	{
		if( $request->ajax() )
		{
			/// my data
			$data = Calendar::where('user_id',Auth::user()->id)->where(function($query) use($request) {
				$query->orwhereDate('start', '>=', $request->start)
					->orwhereDate('end', '<=', $request->end);
			})->get(['id', 'title', 'start', 'end', 'color', 'calendarId','allDay', DB::raw('"true" AS editable')]);

			/* $cycles = Cycle::orwhereDate('start', '>=', $request->start)
				->orwhereDate('end', '<=', $request->end)
				->get(['id', 'title', 'start', 'end',DB::raw('1 AS allDay, "#000" AS color, "c" AS calendarId')]); */

			$events = Event::where('published',1)->where(function($query) use($request){
				$query->orwhereDate('timerdate', '>=', $request->start)
					->orwhereDate('timerdate',   '<=', $request->end);
			})->get(['id', 'title_en AS title', 'timerdate AS start',DB::raw('"#607d8b" AS color, "e" AS calendarId')]);

			if (Auth::user()->can('calendar.events_edit'))
			{
				$public = Calendar::whereNull('isPrivate')->where(function($query) use($request) {
					$query->orwhereDate('start', '>=', $request->start)
						->orwhereDate('end', '<=', $request->end);
				})->get(['id', 'title', 'start', 'end', 'color', 'calendarId','allDay', DB::raw('"true" AS editable')]);
			}
			else
			{
				$public = Calendar::whereNull('isPrivate')->where(function($query) use($request) {
					$query->orwhereDate('start', '>=', $request->start)
						->orwhereDate('end', '<=', $request->end);
				})->get(['id', 'title', 'start', 'end', 'color', 'calendarId','allDay',DB::raw('"p" AS calendarId')]);
			}

			$data = $data->merge($public)->merge($events);
			$mentorshipRequests = MentorshipRequest::get();
			foreach ($mentorshipRequests as $key)
			{
				$data[]=[
					'id'=>$key->id,
					'title'=>$key->mentor->name,
					'start'=>$key->zoom_date,
					'end'=> $key->end_zoom_date,
					'calendarId'=>'M',
					'color'=>'#607d8b',
					'allDay'=>false
				];
			}

			return response()->json($data);
		}
		return view('admin.calendar');
	}

	public function editcal($id)
	{
		if (Auth::user()->can('calendar.events_edit')) { $cal = Calendar::findOrFail($id); }
		else { $cal = Calendar::where('user_id',Auth::user()->id)->findOrFail($id); }
		return view('admin.calendaredit', compact( 'cal'));
	}

	public function updatecal(Request $request, $id)
	{
		$private = 1;
		if (Auth::user()->can('calendar.events_edit')) { $private = $request->isPrivate ? 1: null; }
		Calendar::find($id)->update([
			'title' => $request->title,
			'start' => $request->start,
			'end' => $request->end,
			'body' => $request->body,
			'color' => $request->color,
			'allDay' => $request->allDay?1:null,
			'isPrivate' => $private,
			'user_id' => Auth::user()->id,
		]);
		return redirect()->route('calendar.events_list');
	}

	public function delcal($id)
	{
		if (Auth::user()->can('calendar.events_edit'))
		{
			Calendar::find($id)->delete(); } else {
			Calendar::where('user_id',Auth::user()->id)->find($id)->delete();
		}
		return redirect()->route('calendar.events_list');
	}

	public function ajax(Request $request)
	{
		switch ($request->type)
		{
			case 'add':
				$event = Calendar::create([
					'title' => $request->title,
					'start' => $request->start,
					'end' => $request->end,
					'allDay' => $request->allDay,
					'user_id' => Auth::user()->id,
					'isPrivate' =>1,
				]);
				return response()->json($event);
				break;
			case 'update':
				$event = Calendar::find($request->id)->update([
					'title' => $request->title,
					'start' => $request->start,
					'end' => $request->end,
					'allDay' => $request->allDay,
					'user_id' => Auth::user()->id,
				]);
				return response()->json($event);
				break;
		}
	}

	public function viewcal(Request $request)
	{
		if ($request->id)
		{
			$event = Calendar::findOrFail($request->id);
			return response()->json($event);
		}
		return response()->json(['state' => 'failed'], 500);
	}

	public function update_settingsone(Request $request)
	{
		$request->validate([
			'terms_url' => ['nullable', 'regex:/^https:\/\/(?!-)(?!.*--)(?!.*\.\.)(?!.*\/\/)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?<!-)(\/.*)?$/'],
    		'privacy_url' => ['nullable', 'regex:/^https:\/\/(?!-)(?!.*--)(?!.*\.\.)(?!.*\/\/)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?<!-)(\/.*)?$/'],
			// GV-9566: this is the platform zone every meeting and deadline is read in.
			'timezone' => ['nullable', Rule::in(DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC))],
		]);

		$id = $request->input('id');
		$site_ar_title = $request->input('site_ar_title');
		$site_title = $request->input('site_title');
		$autoact = $request->input('autoact');
		$Platform_Name = $request->input('Platform_Name');
		$Platform_support_email = $request->input('Platform_support_email');
		$Country = $request->input('Country');
		$State = $request->input('State');
		$Default_Language = $request->input('Default_Language');
		$timezone = $request->input('timezone');
		$Currency = $request->input('Currency');
		$homePage = $request->HomePage == 'on' ? '1' : '0';
		$Assessment = $request->Assessment == 'on' ? '1' : '0';
		$MentorList = $request->MentorList == 'on' ? '1' : '0';

		$updateSittings = Settings::where('id', $id)->first();
		$updateSittings->ar_title = $site_ar_title;
		$updateSittings->title = $site_title;
		$request->input('autoact')=='on'?$updateSittings->set_order = 1:$updateSittings->set_order = null;
		$updateSittings->Platform_Name = $Platform_Name;
		$updateSittings->Platform_support_email = $Platform_support_email;
		$updateSittings->Country = $Country;
		$updateSittings->State = $State;
		$updateSittings->Default_Language = $Default_Language;
		$updateSittings->timezone = $timezone;
		$updateSittings->Currency = $Currency;
		$updateSittings->HomePage = $homePage;
		$updateSittings->mentor_list = $MentorList;
		$updateSittings->assessment = $Assessment;
		$updateSittings->terms_url = $request->terms_url;
		$updateSittings->privacy_url = $request->privacy_url;

		$updateSittings->save();
		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

	public function update_settinglogo(Request $request)
	{
		$id = $request->input('id');
		$updateSittings = Settings::where('id', $id)->first();
		$fileManager = app(\App\Services\FileManagerService::class);

		// Validate every uploaded image before replacing any of them.
		$fileManager->assertAllAllowed([
			[$request->file('logo'), 'image'],
			[$request->file('header'), 'image'],
			[$request->file('footer'), 'image'],
		]);

		if( $request->hasFile('logo') )
		{
			$logo = $fileManager->update($request->file('logo'), $updateSittings->Logo, '', 'logo_uploads', 'image');
		} else { $logo = $updateSittings->Logo; }

		if ($request->hasFile('header'))
		{
			$header = $fileManager->update($request->file('header'), $updateSittings->header, '', 'logo_uploads', 'image');
		} else { $header = $updateSittings->header; }

		if ($request->hasFile('footer'))
		{
			$footer = $fileManager->update($request->file('footer'), $updateSittings->footer, '', 'logo_uploads', 'image');
		} else { $footer = $updateSittings->footer; }

		$updateSittings->Logo = $logo;
		$updateSittings->header = $header;
		$updateSittings->footer = $footer;
		$updateSittings->save();
		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

	public function update_settingstwo(Request $request)
	{
		$id = $request->input('id');
		$gtag = $request->input('gtag');

		$updateSittings = Settings::where('id', $id)->first();
		$updateSittings->url = $gtag;
		$updateSittings->save();

		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

	public function update_settingspush(Request $request)
	{
		$id = $request->input('id');
		$BroadCast = $request->input('BroadCast');
		$PUSHER_APP_KEY = $request->input('PUSHER_APP_KEY');
		$PUSHER_APP_ID = $request->input('PUSHER_APP_ID');
		$PUSHER_APP_SECRET = $request->input('PUSHER_APP_SECRET');
		$PUSHER_APP_CLUSTER = $request->input('PUSHER_APP_CLUSTER');
		$MIX_PUSHER_APP_KEY = $request->input('MIX_PUSHER_APP_KEY');
		$MIX_PUSHER_APP_CLUSTER = $request->input('MIX_PUSHER_APP_CLUSTER');
	
		$updateSittings = Settings::where('id', $id)->first();
		$updateSittings->BroadCast = $BroadCast;
		$updateSittings->PUSHER_APP_KEY = $PUSHER_APP_KEY;
		$updateSittings->PUSHER_APP_ID = $PUSHER_APP_ID;
		$updateSittings->PUSHER_APP_SECRET = $PUSHER_APP_SECRET;
		$updateSittings->PUSHER_APP_CLUSTER = $PUSHER_APP_CLUSTER;
		$updateSittings->MIX_PUSHER_APP_KEY = $MIX_PUSHER_APP_KEY;
		$updateSittings->MIX_PUSHER_APP_CLUSTER = $MIX_PUSHER_APP_CLUSTER;
		$updateSittings->save();

		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

	public function update_settingsMeeting(Request $request)
	{
		$id = $request->input('id');
		$Meeting = $request->input('Meeting');
		$MICROSOFT_CLIENT_ID = $request->input('MICROSOFT_CLIENT_ID');
		$MICROSOFT_CLIENT_SECRET_VALUE = $request->input('MICROSOFT_CLIENT_SECRET_VALUE');
		$MICROSOFT_TENANT_ID = $request->input('MICROSOFT_TENANT_ID');
		$MICROSOFT_CLIENT_SECRET_ID = $request->input('MICROSOFT_CLIENT_SECRET_ID');
		$MICROSOFT_APP_EMAIL = $request->input('MICROSOFT_APP_EMAIL');
		$MICROSOFT_REDIRECT_URI = $request->input('MICROSOFT_REDIRECT_URI');
		$ZOOM_CLIENT_ID = $request->input('ZOOM_CLIENT_ID');
		$ZOOM_CLIENT_SECRET = $request->input('ZOOM_CLIENT_SECRET');
		$ZOOM_ACCOUNT_ID = $request->input('ZOOM_ACCOUNT_ID');

		$allow_offline_sessions = $request->input('allow_offline_sessions') == 1 ? 1 : 0;
		$default_offline_session_location = $request->input('default_offline_session_location');
		$map_location = $request->input('map_location');

		$updateSittings = Settings::where('id', $id)->first();
		$updateSittings->Meeting = $Meeting;

		if( strtolower($Meeting) == 'teams' )
		{
			$updateSittings->MICROSOFT_CLIENT_ID = $MICROSOFT_CLIENT_ID;
			$updateSittings->MICROSOFT_CLIENT_SECRET_VALUE = $MICROSOFT_CLIENT_SECRET_VALUE;
			$updateSittings->MICROSOFT_TENANT_ID = $MICROSOFT_TENANT_ID;
			$updateSittings->MICROSOFT_CLIENT_SECRET_ID = $MICROSOFT_CLIENT_SECRET_ID;
			$updateSittings->MICROSOFT_APP_EMAIL = $MICROSOFT_APP_EMAIL;
			$updateSittings->MICROSOFT_REDIRECT_URI = $MICROSOFT_REDIRECT_URI;
			$updateSittings->allow_offline_sessions = $allow_offline_sessions;
			$updateSittings->default_offline_session_location = $default_offline_session_location;
			$updateSittings->map_location = $map_location;
			$updateSittings->max_concurrent_sessions = $request->max_concurrent_sessions;
			$updateSittings->save();
		}
		if( strtolower($Meeting) == 'zoom' )
		{
			$updateSittings->ZOOM_CLIENT_ID = $ZOOM_CLIENT_ID;
			$updateSittings->ZOOM_CLIENT_SECRET = $ZOOM_CLIENT_SECRET;
			$updateSittings->ZOOM_ACCOUNT_ID = $ZOOM_ACCOUNT_ID;
			$updateSittings->allow_offline_sessions = $allow_offline_sessions;
			$updateSittings->default_offline_session_location = $default_offline_session_location;
			$updateSittings->map_location = $map_location;
			$updateSittings->max_concurrent_sessions = $request->max_concurrent_sessions;
			$updateSittings->save();
		}

		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

	public function update_settingsLogin(UpdateSocialLoginRequest  $request)
	{
		$data = $request->validated();
    	$id = $data['id'];

		$GOOGLE_CALENDAR_CLIENT_ID = $request->input('GOOGLE_CALENDAR_CLIENT_ID');
		$GOOGLE_CALENDAR_CLIENT_SECRET = $request->input('GOOGLE_CALENDAR_CLIENT_SECRET');
		$GOOGLE_CALENDAR_REDIRECT = $request->input('GOOGLE_CALENDAR_REDIRECT');


		$VFEmail = $request->input('VerficationEmail');
		$VFEmail1 = $request->VerficationEmail == 'on' ? '1' : '0';
		$updateSittings = Settings::where('id', $id)->first();
		
		$updateSittings->GOOGLE_LOGIN = $request->input('GOOGLE_LOGIN', $updateSittings->GOOGLE_LOGIN ?? 'disabled');

    	if ($updateSittings->GOOGLE_LOGIN === 'enabled') {
        	$updateSittings->GOOGLE_CLIENT_ID = $request->input('GOOGLE_CLIENT_ID');
        	$updateSittings->GOOGLE_CLIENT_SECRET = $request->input('GOOGLE_CLIENT_SECRET');
        	$updateSittings->GOOGLE_REDIRECT = $request->input('GOOGLE_REDIRECT');
    	}

		$updateSittings->GOOGLE_CALENDAR_CLIENT_ID = $GOOGLE_CALENDAR_CLIENT_ID;
		$updateSittings->GOOGLE_CALENDAR_CLIENT_SECRET = $GOOGLE_CALENDAR_CLIENT_SECRET;
		$updateSittings->GOOGLE_CALENDAR_REDIRECT = $GOOGLE_CALENDAR_REDIRECT;

    	$updateSittings->LINKEDIN_LOGIN = $request->input('LINKEDIN_LOGIN', 'disabled');

    	if ($updateSittings->LINKEDIN_LOGIN === 'enabled') {
        	$updateSittings->LINKEDIN_CLIENT_ID = $request->input('LINKEDIN_CLIENT_ID');
        	$updateSittings->LINKEDIN_CLIENT_SECRET = $request->input('LINKEDIN_CLIENT_SECRET');
        	$updateSittings->LINKEDIN_REDIRECT_URI = $request->input('LINKEDIN_REDIRECT_URI');
    	}

		$updateSittings->VFEmail = $VFEmail1;
		$updateSittings->save();
		$activeTab = $request->input('active_tab');

   		switch ($activeTab) {
        	case 'google-oauth':
            	$message = 'google_login_saved';
            	break;

        	case 'google-calendar':
				$message = 'google_calendar_saved';                
           		break;
        	case 'linkedin':
            	$message = 'linkedin_login_saved';
            	break;
        	default:
		 		$message = 'settings_saved';
           		 break;
    	}

    	return redirect()->back()->with('message', $message)->with('active_tab', $activeTab);
	}

	public function update_settingsImgHome(Request $request)
	{
		$Img1 =  Settings::findOrfail('7');
		$Img2 =  Settings::findOrfail('8');
		$Img3 =  Settings::findOrfail('9');
		$fileManager = app(\App\Services\FileManagerService::class);

		// Validate every uploaded image before replacing any of them.
		$fileManager->assertAllAllowed([
			[$request->file('img1'), 'image'],
			[$request->file('img2'), 'image'],
			[$request->file('img3'), 'image'],
		]);
	
		//Image 1 =======================================
		if ($request->hasFile('img1'))
		{
			$img1 = $fileManager->update($request->file('img1'), $Img1->img_url, '', 'Home_upload', 'image');
		} else { $img1 = $Img1->img_url; }

		$ar_img1title = $request->input('ar_img1title');
		$img1title = $request->input('img1title');
		$ar_img1title2 = $request->input('ar_img1title2');
		$img1title2 = $request->input('img1title2');
		$ar_img1dsc = $request->input('ar_img1dsc');
		$img1dsc = $request->input('img1dsc');
		$img1url = $request->input('img1url');

		$updateSittingsImg1 = Settings::where('id', '7')->first();
		$updateSittingsImg1->img_url = $img1;
		$updateSittingsImg1->ar_title = $ar_img1title;
		$updateSittingsImg1->title = $img1title;
		$updateSittingsImg1->ar_set1 = $ar_img1title2;
		$updateSittingsImg1->set1 = $img1title2;
		$updateSittingsImg1->ar_dsc = $ar_img1dsc;
		$updateSittingsImg1->dsc = $img1dsc;
		$updateSittingsImg1->url = $img1url;
		$updateSittingsImg1->save();

		// Image 2 =======================================
		if ($request->hasFile('img2'))
		{
			$img2 = $fileManager->update($request->file('img2'), $Img2->img_url, '', 'Home_upload', 'image');
		} else { $img2 = $Img2->img_url; }

		$ar_img2title = $request->input('ar_img2title');
		$img2title = $request->input('img2title');
		$ar_img2title2 = $request->input('ar_img2title2');
		$img2title2 = $request->input('img2title2');
		$ar_img2dsc = $request->input('ar_img2dsc');
		$img2dsc = $request->input('img2dsc');
		$img2url = $request->input('img2url');

		$updateSittingsImg2 = Settings::where('id', '8')->first();
		$updateSittingsImg2->img_url = $img2;
		$updateSittingsImg2->ar_title = $ar_img2title;
		$updateSittingsImg2->title = $img2title;
		$updateSittingsImg2->ar_set1 = $ar_img2title2;
		$updateSittingsImg2->set1 = $img2title2;
		$updateSittingsImg2->ar_dsc = $ar_img2dsc;
		$updateSittingsImg2->dsc = $img2dsc;
		$updateSittingsImg2->url = $img2url;
		$updateSittingsImg2->save();

		// Image 3 =======================================
		if ($request->hasFile('img3'))
		{
			$img3 = $fileManager->update($request->file('img3'), $Img3->img_url, '', 'Home_upload', 'image');
		} else { $img3 = $Img3->img_url; }
	
		$ar_img3title = $request->input('ar_img3title');
		$img3title = $request->input('img3title');
		$ar_img3title2 = $request->input('ar_img3title2');
		$img3title2 = $request->input('img3title2');
		$ar_img3dsc = $request->input('ar_img3dsc');
		$img3dsc = $request->input('img3dsc');
		$img3url = $request->input('img3url');

		$updateSittingsImg3 = Settings::where('id', '9')->first();
		$updateSittingsImg3->img_url = $img3;
		$updateSittingsImg3->ar_title = $ar_img3title;
		$updateSittingsImg3->title = $img3title;
		$updateSittingsImg3->ar_set1 = $ar_img3title2;
		$updateSittingsImg3->set1 = $img3title2;
		$updateSittingsImg3->ar_dsc = $ar_img3dsc;
		$updateSittingsImg3->dsc = $img3dsc;
		$updateSittingsImg3->url = $img3url;
		$updateSittingsImg3->save();

		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

	public function update_settingsAchievements(Request $request)
	{
		$data1 =  Settings::findOrfail('12');
		$data2 =  Settings::findOrfail('13');
		$data3 =  Settings::findOrfail('14');
		$data4 =  Settings::findOrfail('15');

		// Achievment 12 =======================================
		$ct12icon = $request->input('ct12icon');
		$ct12title = $request->input('ct12title');
		$ct12titlear = $request->input('ct12titlear');
		$ct12num = $request->input(key: 'ct12num');

		$updateSittingsAch12 = Settings::where('id', '12')->first();
		$updateSittingsAch12->dsc = $ct12num;
		$updateSittingsAch12->ar_title = $ct12titlear;
		$updateSittingsAch12->title = $ct12title;
		$updateSittingsAch12->img_url = $ct12icon;
		$updateSittingsAch12->save();

		// Achievment 12 =======================================
		// Achievment 13 =======================================

		$ct13icon = $request->input('ct13icon');
		$ct13title = $request->input('ct13title');
		$ct13titlear = $request->input('ct13titlear');
		$ct13num = $request->input(key: 'ct13num');

		$updateSittingsAch13 = Settings::where('id', '13')->first();
	
		$updateSittingsAch13->img_url = $ct13icon;
		$updateSittingsAch13->title = $ct13title;
		$updateSittingsAch13->ar_title = $ct13titlear;
		$updateSittingsAch13->dsc = $ct13num;
		$updateSittingsAch13->save();

		// Achievment 13 =======================================
		// Achievment 14 =======================================

		$ct14icon = $request->input('ct14icon');
		$ct14title = $request->input('ct14title');
		$ct14titlear = $request->input('ct14titlear');
		$ct14num = $request->input(key: 'ct14num');

		$updateSittingsAch14 = Settings::where('id', '14')->first();

		$updateSittingsAch14->img_url = $ct14icon;
		$updateSittingsAch14->title = $ct14title;
		$updateSittingsAch14->ar_title = $ct14titlear;
		$updateSittingsAch14->dsc = $ct14num;
		$updateSittingsAch14->save();

		// Achievment 14 ======================================= 
		// Achievment 15 =======================================

		$ct15icon = $request->input('ct15icon');
		$ct15title = $request->input('ct15title');
		$ct15titlear = $request->input('ct15titlear');
		$ct15num = $request->input(key: 'ct15num');

		$updateSittingsAch15 = Settings::where('id', '15')->first();

		$updateSittingsAch15->img_url = $ct15icon;
		$updateSittingsAch15->title = $ct15title;
		$updateSittingsAch15->ar_title = $ct15titlear;
		$updateSittingsAch15->dsc = $ct15num;
		$updateSittingsAch15->save();

		// Achievment 15 ======================================= 
		return redirect()->back()->with('message', 'StoredSuccessfully');
	}

    public function update_settingsWhatsapp(Request $request)
    {
    
        $id = $request->input('id');
        $whatsApp_URL_callback = $request->input('whatsApp_URL_callback');
        $whatsapp_api_key = $request->input('whatsapp_api_key');
        // $whatsApp_ACCESS_TOKEN = $request->input('whatsApp_ACCESS_TOKEN');


        $updateSittings = Settings::where('id', $id)->first();

        $updateSittings->whatsApp_URL_callback = $whatsApp_URL_callback;
        $updateSittings->whatsapp_api_key = $whatsapp_api_key;
        // $updateSittings->whatsApp_ACCESS_TOKEN = $whatsApp_ACCESS_TOKEN;
        $updateSittings->save();


        return redirect()->back()->with('message', 'StoredSuccessfully');
    }

    /**
     * Platform Branding (GV-9674). Validation and "#abc" -> "#aabbcc"
     * normalization live in UpdatePlatformBrandingRequest; an empty gradient
     * pair is stored as NULL, which renders the theme gradient.
     */
    public function updatePlatformBranding(UpdatePlatformBrandingRequest $request)
    {
        $settings = Settings::findOrFail(1);
        foreach (PlatformBrandingService::FIELDS as $field => $column) {
            $settings->{$column} = $request->input('branding_' . $field);
        }
        $settings->save();

        return redirect()->back()->with('message', 'StoredSuccessfully')->with('settings_tab', 'branding');
    }

    /** Reset to Default: NULL every color so the stock theme applies again. */
    public function resetPlatformBranding()
    {
        $settings = Settings::findOrFail(1);
        foreach (PlatformBrandingService::FIELDS as $column) {
            $settings->{$column} = null;
        }
        $settings->save();

        return redirect()->back()->with('message', 'StoredSuccessfully')->with('settings_tab', 'branding');
    }

    public function showSettings()
    {
        $user = auth()->user();
        $organization = $this->settingsOrganization();
        $currencies = Currency::where('enabled',1)->get();
        $timezones = collect(\DateTimeZone::listIdentifiers())
        ->mapWithKeys(function ($timezone) {
			$zone = new \DateTimeZone($timezone);
			$offset = $zone->getOffset(new \DateTime("now", $zone)) / 3600; 
			$formattedOffset = ($offset >= 0 ? 'GMT+' : 'GMT') . $offset;
			return [$timezone => $timezone . ' (' . $formattedOffset . ')'];
    	})->sort();
		if(isset($organization) && $organization->id != null){
		$invitations = Invitation::where('organization_id', $organization->id)->get();
		}
		 else {
			$invitations = collect([]);
		}
		$role = Role::where('name', 'organizationManager')->first();
		$emailConfig = $organization ? EmailSettingsStore::forOrganization($organization)->viewData() : null;

        return view('admin.organizations.settings', compact('organization', 'currencies', 'timezones','role','invitations','emailConfig'));
    }

	public function ReminderLogs(Request $request) {
		// $delete = ReminderLog::truncate();
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		if($user->hasAnyRole(['organizationManager', 'subadmin']))
		{
			$cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->get();
			$ReminderLogs = ReminderLog::whereIn('cycle_id', $cycles->pluck('id'))->get();
		}
		elseif($user->hasRole('Admin'))
		{
			$ReminderLogs = ReminderLog::all();
		}
		else
		{
			$ReminderLogs = collect([]);
		}

		$query = $ReminderLogs;

        if ($request->filled('program')) {
            $query->where('cycle_id', $request->program);
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('channel')) {
            $query->where('channel', 'like', '%'. $request->channel. '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $from = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : Carbon::minValue();
            $to = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now();
            $query->whereBetween('created_at', [$from, $to]);
        }

		$ReminderLogs = $query->values();
		$users = Role::whereIn('default_for', ['mentor', 'registered'])->with(['users', 'users.roles', 'users.organizations', 'users.company', 'users.company.cycledata'])->get();
		$users = $users->flatMap(function ($role) {
			return $role->users;
		})->unique('id')->values();

		$organizations = auth()->user()->user_organizations();
		$programs = Cycle::WhereIn('id', $ReminderLogs->pluck('cycle_id'))->get();
		return view('admin.organizations.reminder_logs', compact('ReminderLogs', 'users', 'organizations', 'programs'));
	}
    public function generalSetting(Request $request)
    {
         $request->validate([
            'name_en' => 'nullable|string|max:255',
            'img_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'time_zone' => ['nullable', Rule::in(DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC))],
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        $organization = Organization::find($request->input('id'));

        if (!$organization) {
          return redirect()->back()->with('error', 'Organization not found.');
        }
        abort_unless(auth()->user()->managesOrganization($organization->id), 403);

        $organization->name_en = $request->input('organization_name', $organization->name_en);
        $organization->contact_email = $request->input('contact_email', $organization->contact_email);
        $organization->contact_phone = $request->input('contact_phone', $organization->contact_phone);
        $organization->address = $request->input('address', $organization->address);
        $organization->time_zone = $request->input('time_zone', $organization->time_zone);
        $organization->currency_id = $request->input('currency_id', $organization->currency_id);
        $organization->save();

        if ($request->hasFile('img_url')) {
            $image = $request->file('img_url');
            // Stored name is derived server-side from validated content, never from the client extension.
            $imageName = app(FileManagerService::class)->validatedSafeName($image, 'image');

             $image->move(public_path('organizations/'), $imageName);
             $organization->img_url = 'organizations/' . $imageName;
            $organization->save();
        }

            return redirect()->back()->with('message', 'StoredSuccessfully');

    }

    public function meetingSetting(Request $request)
    {
        $request->validate([
            'meeting_platform' => 'required|string',
            'ZOOM_CLIENT_ID' => 'nullable|string',
            'ZOOM_CLIENT_SECRET' => 'nullable|string',
            'ZOOM_ACCOUNT_ID' => 'nullable|string',
            'MICROSOFT_CLIENT_ID' => 'nullable|string',
            'MICROSOFT_CLIENT_SECRET_VALUE' => 'nullable|string',
            'MICROSOFT_TENANT_ID' => 'nullable|string',
            'MICROSOFT_REDIRECT_URI' => 'nullable|string',
            'max_concurrent_sessions' => 'nullable|integer',
        ]);

        $organization = Organization::find($request->input('id'));

        if (!$organization) {
            return redirect()->back()->with('error', 'Organization not found.');
        }
        abort_unless(auth()->user()->managesOrganization($organization->id), 403);

        $organization->meeting_platform = $request->input('meeting_platform');
        $organization->max_concurrent_sessions = $request->input('max_concurrent_sessions');

        if ($request->input('meeting_platform') === 'zoom') {
            $organization->zoom_client_id = $request->input('ZOOM_CLIENT_ID');
            $organization->zoom_client_secret = $request->input('ZOOM_CLIENT_SECRET');
            $organization->zoom_account_id = $request->input('ZOOM_ACCOUNT_ID');
        } elseif ($request->input('meeting_platform') === 'teams') {
            $organization->microsoft_client_id = $request->input('MICROSOFT_CLIENT_ID');
            $organization->microsoft_client_secret_value = $request->input('MICROSOFT_CLIENT_SECRET_VALUE');
            $organization->microsoft_tenant_id = $request->input('MICROSOFT_TENANT_ID');
            $organization->microsoft_client_secret_id = $request->input('MICROSOFT_CLIENT_SECRET_ID');
            $organization->microsoft_app_email = $request->input('MICROSOFT_APP_EMAIL');
            $organization->microsoft_redirect_uri = $request->input('MICROSOFT_REDIRECT_URI');
        }
          
        $organization->save();

        return redirect()->back()->with('message', 'StoredSuccessfully');
  
    }


    public function socialSetting(Request $request)
    {
        $request->validate([
            'website_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'twitter_handle' => 'nullable|string',
            'youtube_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
        ]);
    
        $organization = Organization::find($request->input('id'));

        if (!$organization) {
            return redirect()->back()->with('error', 'Organization not found.');
        }
        abort_unless(auth()->user()->managesOrganization($organization->id), 403);

        $organization->website_url = $request->input('website_url');
        $organization->facebook_page_url = $request->input('facebook_url');
        $organization->twitter_handle = $request->input('twitter_handle');
        $organization->youtube_channel_url = $request->input('youtube_url');
        $organization->instagram_profile_url = $request->input('instagram_url');
        $organization->linkedin_company_url = $request->input('linkedin_url');
        $organization->tiktok_url = $request->input('tiktok_url');
        $organization->save();
    
        return redirect()->back()->with('message', 'StoredSuccessfully');

    }

		

	public function inviteUser(Request $request)
	{
			
		$validator = Validator::make($request->all(), [
			'first_name' => 'required|string',
			'last_name' => 'required|string',
			'email' => 'required|email|unique:users,email',
			// Only organization-manager invitations exist (team-members form); never admin/sub-admin.
			'role' => ['required', 'string', Rule::exists('roles', 'name')->where('default_for', 'organizationmanager')],
		]);
	
		if ($validator->fails()) {
			return back()->with('message2', $validator->errors()->first())->withInput();
		}

		// Invite into the organization this Settings page manages, never the user's first one.
		$organization = $this->settingsOrganization();
		if (!$organization) {
			return back()->with('message2', __('settings.no_organization_linked'))->withInput();
		}

		// Check if user already exists in another organization
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$existingUser = User::where('email', $request->email)->first();
		if ($existingUser && !empty($orgIds) && !in_array($existingUser->organization_id, $orgIds)) {
			return back()->with('message2', __('admin.user_belongs'));
		}

		// Check for existing invitation and allow resending
		$invitation = Invitation::where('email', $request->email)
			->where('organization_id', $organization->id)
			->first();

		if (!$invitation) {
			$invitation = new Invitation();
			$invitation->email = $request->email;
			$invitation->role = $request->role;
			$invitation->organization_id = $organization->id;
		}

		$invitation->first_name = $request->first_name;
		$invitation->last_name = $request->last_name;
		$invitation->expires_at = now()->addDays(2);
		$invitation->save();

		$this->sendInvitation($invitation);

		return back()->with('message', 'InvitationSent');
	}

	/**
	 * The organization the Settings page shows and manages for the current user.
	 */
	private function settingsOrganization(): ?Organization
	{
		$user = auth()->user();

		return $user->organization ?? Organization::whereIn('id', $user->organizations_ids)->first();
	}

	/**
	 * Email the invitation. Sent only after it is saved; a mail/queue failure is
	 * logged and must not undo the invitation (it can be resent from the list).
	 */
	private function sendInvitation(Invitation $invitation): void
	{
		try {
			Notification::route('mail', $invitation->email)
				->notify(new UserInvitationNotification($invitation));
		} catch (\Throwable $e) {
			Log::error('UserInvitationNotification failed: ' . $e->getMessage(), [
				'invitation_id' => $invitation->id,
				'organization_id' => $invitation->organization_id,
			]);
		}
	}
	 
	public function showSetPasswordForm($invitationId)
	{
		$invitation = Invitation::findOrFail($invitationId);

		if ($invitation->status !== 'pending') {
			return redirect()->route('login')->with('message2', __('admin.invitationAlreadyUsed'));
		}

		if ($invitation->expires_at < now()) {
			return redirect()->route('login')->with('message2', __('admin.invitationExpired'));
		}

		return view('auth.set-password', compact('invitation'));
	}

	public function acceptInvitation(Request $request, $invitationId)
	{
		$request->validate([
			'password' => 'required|min:8|confirmed',
		]);

		$invitation = Invitation::findOrFail($invitationId);

		// Single use: an accepted or cancelled invitation cannot create another account.
		if ($invitation->status !== 'pending') {
			return redirect()->route('login')->with('message2', __('admin.invitationAlreadyUsed'));
		}

		// If the invitation has expired
		if ($invitation->expires_at < now()) {
			return redirect()->back()->withErrors(['error' => __('admin.invitationExpired')]);
		}

		// If the user already exists
		if (User::where('email', $invitation->email)->exists()) {
			return redirect()->back()->withErrors(['error' => __('admin.user_exists')]);
		}

		$user = User::create([
			'first_name_en' => $invitation->first_name,
			'last_name_en' => $invitation->last_name,
			'email' => $invitation->email,
			'password' => bcrypt($request->password),
		]);
		// organization_id is not mass-assignable on User (GV-2017), so create() silently dropped it.
		$user->organization_id = $invitation->organization_id;
		// The signed link was opened from the invited mailbox, so the address is verified.
		$user->email_verified_at = now();
		$user->save();

		// Same membership as a manager created or assigned in the organization wizard.
		$user->organizations()->syncWithoutDetaching([$invitation->organization_id]);

		 $role = Role::where('name', $invitation->role)->first();
		 if ($role) {
			 DB::table('model_has_roles')->insert([
				 'role_id' => $role->id,
				 'model_id' => $user->id,
				 'model_type' => User::class,
			 ]);
		 }

		$user->assignRole($invitation->role);

		$uniqueCode = random_int(100000, 999999);

		$invitation->update([
			'user_id' => $user->id,
			'code' => $uniqueCode,
			'status' => 'accepted',
		]);

		// "Accept Invitation" ends on the invitee's own dashboard, already signed in.
		Auth::login($user);
		$request->session()->regenerate();

		return redirect()->route('core.dashboard_view')->with('message', __('admin.accountCreated'));
	}

	public function resendInvitation($invitationId)
	{
		$invitation = Invitation::findOrFail($invitationId);
		abort_unless(auth()->user()->managesOrganization($invitation->organization_id), 403);

		// Prevent resending if invitation is expired
		if ($invitation->expires_at < now()) {
			return back()->with('message2', __('admin.invitationExpired'));
		}

		// Prevent resending if user has already registered
		if (User::where('email', $invitation->email)->exists()) {
			return back()->with('message2', __('admin.user_already_registered'));
		}

		$invitation->expires_at = now()->addDays(2);
		$invitation->save();

		$this->sendInvitation($invitation);

		return back()->with('message', 'InvitationResent');
	}

	public function cancelInvitation($invitationId)
	{
		$invitation = Invitation::findOrFail($invitationId);
		abort_unless(auth()->user()->managesOrganization($invitation->organization_id), 403);
		$invitation->delete();

		return back()->with('message', 'InvitationCanceled');
	}

	public function industrySettings(Request $request)
		{
			$l = app()->getLocale();
			$technologies = DB::table('technologies')->get()->map(function ($tech) {
								return [
									'id'   => $tech->id,
									'name' => $tech->name,
									'created_at' => $tech->created_at
								];
							})->values()->toArray();

			$allSectors = DB::table('sectors')->get();

			$industries = StaticCategory::with('sectors')->get();
			$data = $industries->map(function ($industry) use ($l, $allSectors) {

				if ($industry->sectors->isNotEmpty()) {
					$sectorsList = $industry->sectors->map(function ($sector) use ($l) {
						return [
							'id'   => $sector->id,
							'name' => $sector->{"name_$l"} ?? $sector->name_en ?? 'N/A',
							'created_at' => $sector->created_at
						];
					})->values()->toArray();
				} else {
					// $sectorsList = $allSectors->map(function ($sector) use ($l) {
					// 	return [
					// 		'id'   => $sector->id,
					// 		'name' => $sector->{"name_$l"} ?? $sector->name_en ?? 'N/A',
					// 		'created_at' => $sector->created_at
					// 	];
					// })->values()->toArray();
					$sectorsList = [];
				}
				return [
					'id'       => $industry->id,
					'industry' => $industry->{'name_'.$l} ?? $industry->name_en ?? 'N/A',
					'name_en'  => $industry->name_en,
					'name_ar'  => $industry->name_ar,
					'created_at' => $industry->created_at,
					'sectors'  => $sectorsList,
				];
				
			});
			
		
			return view('admin.settings.industry.index', compact('data', 'technologies'));
		}
		
	// public function expertiseSettings(Request $request) 
	// {		
	// 	return view('admin.settings.expertise.index');
	// }

	/**
	 * Display notification settings page
	 */
	public function notificationSettings(Request $request)
	{
		$settings = \App\Settings::find(1);
		
		if (!$settings) {
			$settings = new \App\Settings();
			$settings->id = 1;
			$settings->enable_email = true;
			$settings->enable_whatsapp = true;
			$settings->enable_pusher = true;
			$settings->save();
		}

		return view('admin.settings.notifications.index', compact('settings'));
	}

	/**
	 * Update notification settings
	 */
	public function updateNotificationSettings(Request $request)
	{
		$request->validate([
			'enable_email' => 'nullable|in:0,1',
			'enable_whatsapp' => 'nullable|in:0,1',
			'enable_pusher' => 'nullable|in:0,1',
		]);

		$settings = \App\Settings::find(1);
		
		if (!$settings) {
			$settings = new \App\Settings();
			$settings->id = 1;
		}

		$settings->enable_email = $request->has('enable_email') ? true : false;
		$settings->enable_whatsapp = $request->has('enable_whatsapp') ? true : false;
		$settings->enable_pusher = $request->has('enable_pusher') ? true : false;
		$settings->save();

		return redirect()->back()->with('message', 'Notification settings updated successfully');
	}
}
