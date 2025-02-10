<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Cycle;
use App\Event;
use App\State;
use App\Country;
use App\Calendar;
use App\Currency;
use App\Messages;
use App\Settings;
use Carbon\Carbon;
use DB, File, URL;
use App\Organization;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Storage;


class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get_settings(Request $request)
    {
        $this->preProcessingCheck('edit_settings');
        $Title = Settings::findOrfail('1');
        $Icons = Settings::where('type', '2')->get();
        $Img1 =  Settings::findOrfail('7');
        $Img2 =  Settings::findOrfail('8');
        $Img3 =  Settings::findOrfail('9');
        $Counters = Settings::where('type', '6')->get();
        $countries = Country::all();
        $currencies = Currency::all();
        $states = State::all();
        $json = File::get(base_path('database/dbFiles/countries.json'));
        $_countries = json_decode($json, true);
        $_countries = collect($_countries)->map(function ($country) {
            $country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
            return $country;
        });
        $countries = $countries->map(function ($country) use ($_countries) {
            $getCountry = $_countries->where('iso3', $country->iso3)->first();
            $flag = null;
            $emoji = null;
            if (!empty($getCountry)) {
                $flag = $getCountry['flag'];
                $emoji = $getCountry['emoji'];
            }
            $country->flag = $flag;
            $country->emoji = $emoji;
            return $country;
        });
        return view('admin.settings.editegeneral')
            ->with('Title', $Title)->with('Icons', $Icons)->with('Img1', $Img1)
            ->with('Img2', $Img2)->with('Img3', $Img3)->with('Counters', $Counters)
            ->with('countries', $countries)->with('states', $states)->with('currencies',$currencies);
    }

    public function update_settings(Request $request)
    {
        $this->preProcessingCheck('edit_settings');
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
    public function get_aboutus(Request $request)
    {
        $this->preProcessingCheck('edit_settings');
        $allabout = Settings::findorfail('10');
        return view('admin.settings.editaboutus')->with('allabout', $allabout);
    }
    public function update_aboutus(Request $request)
    {
        $this->preProcessingCheck('edit_settings');
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
        $this->preProcessingCheck('edit_settings');
        $allcontact = Settings::findorfail('11');
        return view('admin.settings.editcontactus')->with('allcontact', $allcontact);
    }
    public function update_contactus(Request $request)
    {
        $this->preProcessingCheck('edit_settings');
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
        $this->preProcessingCheck('view_messages');
        $getmsgs = messages::orderBy('read')->orderBy('created_at', 'DESC')->get(['id', 'name', 'email', 'subject', 'read', 'internal_id', 'replyBy']);
        return view('admin.settings.messages')
            ->with('getmsgs', $getmsgs);
    }
    //// Send reply via mail
    public function msgreply(Request $request)
    {
        $this->preProcessingCheck('view_messages');
        /// Send Mail
        $Email = $request->remail;
        if ($Email == filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $user = new User();
            $user->email = $Email;
            $user->id = 1;
            $rfullname = $request->rname;
            $MSG = new TemplateEmail;
            $MSG->Message = [$request->subject . '- VI portal', "Dear " . $rfullname . ",", $request->message, "/dashboard"];
            $user->notify($MSG);
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
        $this->preProcessingCheck('view_messages');
        $getmsg = messages::findOrFail($id);
        $getmsg->read = 1;
        $getmsg->user_id = Auth::user()->id;
        $getmsg->Save();
        $user = [];
        if ($getmsg->replyBy) {
            $user = User::where('id', $getmsg->replyBy)->first(['first_name_ar', 'last_name_ar', 'first_name_en', 'last_name_en']);
        }
        return response()->json([$getmsg, $user]);
    }
    public function deletemsg($id)
    {
        $this->preProcessingCheck('delete_messages');
        $read = messages::findOrfail($id);
        $read->user_id = Auth::user()->id;
        $read->Save();
        $read->delete();
        return redirect('dashboard/mymsg')->with('message', 'DeletedSuccessfully');
    }
    public function makeread($id)
    {
        $this->preProcessingCheck('view_messages');
        $read = messages::findOrfail($id);
        $read->read = 1;
        $read->user_id = Auth::user()->id;
        $read->Save();
        return redirect()->back();
    }
    public function makeunread($id)
    {
        $this->preProcessingCheck('view_messages');
        $read = messages::findOrfail($id);
        $read->user_id = Auth::user()->id;
        $read->read = 0;
        $read->Save();
        return redirect()->back();
    }
    public function calendar(Request $request)
    {
        if ($request->ajax()) {
            /// my data
            //        $data = Calendar::where('user_id',Auth::user()->id)->where(function($query) use($request) {
            //            $query->orwhereDate('start', '>=', $request->start)
            //                ->orwhereDate('end', '<=', $request->end);
            //        })->get(['id', 'title', 'start', 'end', 'color', 'calendarId','allDay', DB::raw('"true" AS editable')]);
            //   /*     $cycles = Cycle::orwhereDate('start', '>=', $request->start)
            //            ->orwhereDate('end', '<=', $request->end)
            //            ->get(['id', 'title', 'start', 'end',DB::raw('1 AS allDay, "#000" AS color, "c" AS calendarId')]); */
            //        $events = Event::where('published',1)->where(function($query) use($request){
            //            $query->orwhereDate('timerdate', '>=', $request->start)
            //                ->orwhereDate('timerdate',   '<=', $request->end);
            //        })->get(['id', 'title_en AS title', 'timerdate AS start',DB::raw('"#607d8b" AS color, "e" AS calendarId')]);
            //        if (Auth::user()->can('edit_calendar')) {
            //            $public = Calendar::whereNull('isPrivate')->where(function($query) use($request) {
            //                $query->orwhereDate('start', '>=', $request->start)
            //                    ->orwhereDate('end', '<=', $request->end);
            //            })->get(['id', 'title', 'start', 'end', 'color', 'calendarId','allDay', DB::raw('"true" AS editable')]);
            //        } else {
            //            $public = Calendar::whereNull('isPrivate')->where(function($query) use($request) {
            //                $query->orwhereDate('start', '>=', $request->start)
            //                    ->orwhereDate('end', '<=', $request->end);
            //            })->get(['id', 'title', 'start', 'end', 'color', 'calendarId','allDay',DB::raw('"p" AS calendarId')]);
            //        }
            //        $data = $data->merge($public)->merge($events);

            $mentorshipRequests = MentorshipRequest::get();
            foreach ($mentorshipRequests as $key) {
                $data[] = [
                    'id' => $key->id,
                    'title' => $key->mentor->name,
                    'start' => $key->zoom_date,
                    'end' => $key->end_zoom_date,
                    'calendarId' => 'M',
                    'color' => '#607d8b',
                    'allDay' => false
                ];
            }

            return response()->json($data);
        }
        return view('admin.calendar');
    }
    public function editcal($id)
    {
        if (Auth::user()->can('edit_calendar')) {
            $cal = Calendar::findOrFail($id);
        } else {
            $cal = Calendar::where('user_id', Auth::user()->id)->findOrFail($id);
        }
        return view('admin.calendaredit', compact('cal'));
    }
    public function updatecal(Request $request, $id)
    {
        $private = 1;
        if (Auth::user()->can('edit_calendar')) {
            $private = $request->isPrivate ? 1 : null;
        }
        Calendar::find($id)->update([
            'title' => $request->title,
            'start' => $request->start,
            'end' => $request->end,
            'body' => $request->body,
            'color' => $request->color,
            'allDay' => $request->allDay ? 1 : null,
            'isPrivate' => $private,
            'user_id' => Auth::user()->id,
        ]);
        return redirect()->route('calendar');
    }
    public function delcal($id)
    {
        if (Auth::user()->can('edit_calendar')) {
            Calendar::find($id)->delete();
        } else {
            Calendar::where('user_id', Auth::user()->id)->find($id)->delete();
        }
        return redirect()->route('calendar');
    }
    public function ajax(Request $request)
    {

        switch ($request->type) {
            case 'add':
                $event = Calendar::create([
                    'title' => $request->title,
                    'start' => $request->start,
                    'end' => $request->end,
                    'allDay' => $request->allDay,
                    'user_id' => Auth::user()->id,
                    'isPrivate' => 1,
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
        if ($request->id) {
            $event = Calendar::findOrFail($request->id);
            return response()->json($event);
        }
        return response()->json([
            'state' => 'failed'
        ], 500);
    }


    public function update_settingsone(Request $request)
    {

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


        if ($request->HomePage == 'on') {
            $homePage = '1';
        } else {
            $homePage = '0';
        }

        if ($request->MentorList == 'on') {
            $MentorList = '1';
        } else {
            $MentorList = '0';
        }

        $updateSittings = Settings::where('id', $id)->first();

        $updateSittings->ar_title = $site_ar_title;
        $updateSittings->title = $site_title;
        $request->input('autoact') == 'on' ? $updateSittings->set_order = 1 : $updateSittings->set_order = null;
        $updateSittings->Platform_Name = $Platform_Name;
        $updateSittings->Platform_support_email = $Platform_support_email;
        $updateSittings->Country = $Country;
        $updateSittings->State = $State;
        $updateSittings->Default_Language = $Default_Language;
        $updateSittings->timezone = $timezone;
        $updateSittings->Currency = $Currency;
        $updateSittings->HomePage = $homePage;
        $updateSittings->mentor_list = $MentorList;
        $updateSittings->save();


        return redirect()->back()->with('message', 'StoredSuccessfully');
    }



    public function update_settinglogo(Request $request)
    {
        $id = $request->input('id');
        $updateSittings = Settings::where('id', $id)->first();

        if (!$updateSittings) {
            return redirect()->back()->with('error', 'Settings not found.');
        }

        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoName = 'logo.' . $logoFile->getClientOriginalExtension();

            $logoFile->move(public_path('images/logo'), $logoName);
        } else {
            $logoName = $updateSittings->Logo;
        }

        if ($request->hasFile('header')) {
            $headerFile = $request->file('header');
            $headerName = 'mailheader.' . $headerFile->getClientOriginalExtension();

            $headerFile->move(public_path('images/logo'), $headerName);
        } else {
            $headerName = $updateSittings->header;
        }

        if ($request->hasFile('footer')) {
            $footerFile = $request->file('footer');
            $footerName = 'mailfooter.' . $footerFile->getClientOriginalExtension();


            $footerFile->move(public_path('images/logo'), $footerName);
        } else {
            $footerName = $updateSittings->footer;
        }

        // Update the settings with the new file names
        $updateSittings->Logo = $logoName;
        $updateSittings->header = $headerName;
        $updateSittings->footer = $footerName;
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

    public function update_settingsmail(Request $request)
    {


        $id = $request->input('id');
        $host = $request->input('host');
        $Mail = $request->input('Mail');
        $Mailpassword = $request->input('Mailpassword');
        $Port = $request->input('Port');

        $updateSittings = Settings::where('id', $id)->first();
        $updateSittings->host = $host;
        $updateSittings->Mail = $Mail;
        $updateSittings->Mailpassword = $Mailpassword;
        $updateSittings->Port = $Port;
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


        $updateSittings = Settings::where('id', $id)->first();
        $updateSittings->Meeting = $Meeting;

        if ($Meeting == 'Teams') {
            $updateSittings->MICROSOFT_CLIENT_ID = $MICROSOFT_CLIENT_ID;
            $updateSittings->MICROSOFT_CLIENT_SECRET_VALUE = $MICROSOFT_CLIENT_SECRET_VALUE;
            $updateSittings->MICROSOFT_TENANT_ID = $MICROSOFT_TENANT_ID;
            $updateSittings->MICROSOFT_CLIENT_SECRET_ID = $MICROSOFT_CLIENT_SECRET_ID;
            $updateSittings->MICROSOFT_APP_EMAIL = $MICROSOFT_APP_EMAIL;
            $updateSittings->MICROSOFT_REDIRECT_URI = $MICROSOFT_REDIRECT_URI;
            $updateSittings->save();
        }
        if ($Meeting == 'Zoom') {
            $updateSittings->ZOOM_CLIENT_ID = $ZOOM_CLIENT_ID;
            $updateSittings->ZOOM_CLIENT_SECRET = $ZOOM_CLIENT_SECRET;
            $updateSittings->ZOOM_ACCOUNT_ID = $ZOOM_ACCOUNT_ID;
            $updateSittings->save();
        }


        return redirect()->back()->with('message', 'StoredSuccessfully');
    }


    public function update_settingsLogin(Request $request)
    {

        $id = $request->input('id');
        $GOOGLE_CLIENT_ID = $request->input('GOOGLE_CLIENT_ID');
        $GOOGLE_CLIENT_SECRET = $request->input('GOOGLE_CLIENT_SECRET');
        $GOOGLE_REDIRECT = $request->input('GOOGLE_REDIRECT');

        $LINKEDIN_CLIENT_ID = $request->input('LINKEDIN_CLIENT_ID');
        $LINKEDIN_CLIENT_SECRET = $request->input('LINKEDIN_CLIENT_SECRET');
        $LINKEDIN_REDIRECT_URI = $request->input('LINKEDIN_REDIRECT_URI');

        $VFEmail = $request->input('VerficationEmail');
        if ($request->VerficationEmail == 'on') {
            $VFEmail1 = '1';
        } else {
            $VFEmail1 = '0';
        }

        $updateSittings = Settings::where('id', $id)->first();

        $updateSittings->GOOGLE_CLIENT_ID = $GOOGLE_CLIENT_ID;
        $updateSittings->GOOGLE_CLIENT_SECRET = $GOOGLE_CLIENT_SECRET;
        $updateSittings->GOOGLE_REDIRECT = $GOOGLE_REDIRECT;

        $updateSittings->LINKEDIN_CLIENT_ID = $LINKEDIN_CLIENT_ID;
        $updateSittings->LINKEDIN_CLIENT_SECRET = $LINKEDIN_CLIENT_SECRET;
        $updateSittings->LINKEDIN_REDIRECT_URI = $LINKEDIN_REDIRECT_URI;

        $updateSittings->VFEmail = $VFEmail1;
        $updateSittings->save();


        return redirect()->back()->with('message', 'StoredSuccessfully');
    }


    public function update_settingsImgHome(Request $request)
    {

        $Img1 =  Settings::findOrfail('7');
        $Img2 =  Settings::findOrfail('8');
        $Img3 =  Settings::findOrfail('9');



        //Image 1 =======================================
        if ($request->hasFile('img1')) {
            $image = $request->file('img1');
            // الحصول على اسم الصورة الأصلي بدون الامتداد
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            // تعديل اسم الصورة الأصلي - يمكنك تغيير الاسم بالطريقة التي تريدها
            // هنا نضيف "new_" كبادئة لاسم الصورة
            $newName = $originalName;
            // تحديد الامتداد الأصلي للصورة
            $extension = $image->getClientOriginalExtension();
            // تركيب الاسم الجديد مع الامتداد
            $img1name = $newName . '.' . $extension;
            // استخدام storeAs بدلاً من move لنقل الصورة إلى المسار المطلوب
            $img1 = $image->storeAs('', $img1name, 'Home_upload');
        } else {
            $img1 = $Img1->img_url; // إذا لم يتم رفع صورة، يمكنك التعامل معها هنا
        }


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


        //Image 2 =======================================
        if ($request->hasFile('img2')) {
            $image = $request->file('img2');
            // الحصول على اسم الصورة الأصلي بدون الامتداد
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            // تعديل اسم الصورة الأصلي - يمكنك تغيير الاسم بالطريقة التي تريدها
            // هنا نضيف "new_" كبادئة لاسم الصورة
            $newName = $originalName;
            // تحديد الامتداد الأصلي للصورة
            $extension = $image->getClientOriginalExtension();
            // تركيب الاسم الجديد مع الامتداد
            $img2name = $newName . '.' . $extension;
            // استخدام storeAs بدلاً من move لنقل الصورة إلى المسار المطلوب
            $img2 = $image->storeAs('', $img2name, 'Home_upload');
        } else {
            $img2 = $Img2->img_url; // إذا لم يتم رفع صورة، يمكنك التعامل معها هنا
        }
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


        //Image 3 =======================================

        if ($request->hasFile('img3')) {
            $image = $request->file('img3');
            // الحصول على اسم الصورة الأصلي بدون الامتداد
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            // تعديل اسم الصورة الأصلي - يمكنك تغيير الاسم بالطريقة التي تريدها
            // هنا نضيف "new_" كبادئة لاسم الصورة
            $newName = $originalName;
            // تحديد الامتداد الأصلي للصورة
            $extension = $image->getClientOriginalExtension();
            // تركيب الاسم الجديد مع الامتداد
            $img3name = $newName . '.' . $extension;
            // استخدام storeAs بدلاً من move لنقل الصورة إلى المسار المطلوب
            $img3 = $image->storeAs('', $img3name, 'Home_upload');
        } else {
            $img3 = $Img3->img_url; // إذا لم يتم رفع صورة، يمكنك التعامل معها هنا
        }


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


        //Achievment 12 =======================================
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

        //Achievment 12 =======================================
        //Achievment 13 =======================================

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

        //Achievment 13 =======================================
        //Achievment 14 =======================================

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

        //Achievment 14 ======================================= 
        //Achievment 15 =======================================

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

        //Achievment 15 ======================================= 


        return redirect()->back()->with('message', 'StoredSuccessfully');
    }

    public function update_settingsWhatsapp(Request $request)
    {
      
        $id = $request->input('id');
        $whatsApp_URL_callback = $request->input('whatsApp_URL_callback');
        $whatsApp_INSTANCE_ID = $request->input('whatsApp_INSTANCE_ID');
        $whatsApp_ACCESS_TOKEN = $request->input('whatsApp_ACCESS_TOKEN');


        $updateSittings = Settings::where('id', $id)->first();

        $updateSittings->whatsApp_URL_callback = $whatsApp_URL_callback;
        $updateSittings->whatsApp_INSTANCE_ID = $whatsApp_INSTANCE_ID;
        $updateSittings->whatsApp_ACCESS_TOKEN = $whatsApp_ACCESS_TOKEN;
        $updateSittings->save();


        return redirect()->back()->with('message', 'StoredSuccessfully');
    }


    public function showSettings()
    {
        $organization = auth()->user()->organization; 
        $currencies = Currency::all();  
        $timezones = collect(\DateTimeZone::listIdentifiers())
        ->mapWithKeys(function ($timezone) {
        $zone = new \DateTimeZone($timezone);
        $offset = $zone->getOffset(new \DateTime("now", $zone)) / 3600; 
        $formattedOffset = ($offset >= 0 ? 'GMT+' : 'GMT') . $offset;
        return [$timezone => $timezone . ' (' . $formattedOffset . ')'];
    })->sort();

        return view('admin.organizations.settings', compact('organization', 'currencies', 'timezones'));
    }


    public function generalSetting(Request $request)
    {
      $request->validate([
        'name_en' => 'nullable|string|max:255',
        'img_url' => 'nullable|image',
        'contact_email' => 'nullable|email',
        'contact_phone' => 'nullable|string|max:11',
        'address' => 'nullable|string',
        'time_zone' => 'nullable|string|max:100',
        'currency_id' => 'nullable|exists:currencies,id',
    ]);

    $organization = Organization::find($request->input('id'));

    if (!$organization) {
        return redirect()->route('organization-settings')->with('error', 'Organization not found.');
    }

    $organization->name_en = $request->input('organization_name', $organization->name_en);
    $organization->contact_email = $request->input('contact_email', $organization->contact_email);
    $organization->contact_phone = $request->input('contact_phone', $organization->contact_phone);
    $organization->address = $request->input('address', $organization->address);
    $organization->time_zone = $request->input('time_zone', $organization->time_zone);
    $organization->currency_id = $request->input('currency_id', $organization->currency_id);
    $organization->save();

    if ($request->hasFile('img_url')) {
        $image = $request->file('img_url');
        $imageName = time() . '.' . $image->getClientOriginalExtension();

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
        ]);

        $organization = Organization::find($request->input('id'));
        $organization->meeting_platform = $request->input('meeting_platform');
        $organization->zoom_client_id = $request->input('ZOOM_CLIENT_ID');
        $organization->zoom_client_secret = $request->input('ZOOM_CLIENT_SECRET');
        $organization->zoom_account_id = $request->input('ZOOM_ACCOUNT_ID');    
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
}


