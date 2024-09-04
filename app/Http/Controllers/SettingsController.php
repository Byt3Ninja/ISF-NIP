<?php
namespace App\Http\Controllers;
use App\Calendar;
use App\Country;
use App\Cycle;
use App\MentorshipRequest;
use App\Notifications\TemplateEmail;
Use App\Settings;
Use App\Messages;
use App\User;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Auth;
use App\Event;
use App\State;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
	public function get_settings(Request $request){
            $this->preProcessingCheck('edit_settings');
			$Title = Settings::findOrfail('1');
			$Icons = Settings::where('type','2')->get();
			$Img1 =  Settings::findOrfail('7');
			$Img2 =  Settings::findOrfail('8');
			$Img3 =  Settings::findOrfail('9');
			$Counters = Settings::where('type','6')->get();
            $countries = Country::all();
            $states= State::all();
            return view('admin.settings.editegeneral')
                ->with('Title',$Title)->with('Icons',$Icons)->with('Img1',$Img1)
				->with('Img2',$Img2)->with('Img3',$Img3)->with('Counters',$Counters)
                ->with('countries',$countries)->with('states',$states);
    }
    public function update_settings(Request $request){
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
public function get_aboutus(Request $request){
            $this->preProcessingCheck('edit_settings');
			$allabout = Settings::findorfail('10');
            return view('admin.settings.editaboutus')->with('allabout',$allabout);
    }
public function update_aboutus(Request $request){
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
public function get_contactus(Request $request){
            $this->preProcessingCheck('edit_settings');
			$allcontact = Settings::findorfail('11');
            return view('admin.settings.editcontactus')->with('allcontact',$allcontact);
    }
public function update_contactus(Request $request){
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
    public function mymsg(){
            $this->preProcessingCheck('view_messages');
			$getmsgs = messages::orderBy('read')->orderBy('created_at','DESC')->get(['id','name','email','subject','read','internal_id','replyBy']);
            return view('admin.settings.messages')
                ->with('getmsgs',$getmsgs);
    }
    //// Send reply via mail
    public function msgreply(Request $request){
        $this->preProcessingCheck('view_messages');
        /// Send Mail
        $Email = $request->remail;
        if ($Email == filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $user = new User();
            $user->email = $Email;
            $user->id = 1;
            $rfullname = $request->rname;
            $MSG = new TemplateEmail;
            $MSG->Message = [$request->subject.'- VI portal', "Dear " . $rfullname . ",", $request->message, "/dashboard"];
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
    public function openonemsg($id){
        $this->preProcessingCheck('view_messages');
        $getmsg = messages::findOrFail($id);
        $getmsg->read = 1;
        $getmsg->user_id = Auth::user()->id;
        $getmsg->Save();
        $user=[];
        if ($getmsg->replyBy) {$user = User::where('id',$getmsg->replyBy)->first(['first_name_ar','last_name_ar','first_name_en','last_name_en']);}
        return response()->json([$getmsg,$user]);
    }
		public function deletemsg($id){
            $this->preProcessingCheck('delete_messages');
            $read = messages::findOrfail($id);
            $read->user_id = Auth::user()->id;
            $read->Save();
            $read->delete();
            return redirect('dashboard/mymsg')->with('message', 'DeletedSuccessfully');
    }
			public function makeread($id){
            $this->preProcessingCheck('view_messages');
			$read = messages::findOrfail($id);
			$read->read = 1;
			$read->user_id = Auth::user()->id;
			$read->Save();
            return redirect()->back();
    }
            public function makeunread($id){
            $this->preProcessingCheck('view_messages');
            $read = messages::findOrfail($id);
            $read->user_id = Auth::user()->id;
            $read->read = 0;
            $read->Save();
            return redirect()->back();
    }
public function calendar(Request $request){
    if($request->ajax()) {
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
        foreach ($mentorshipRequests as $key){
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
        if (Auth::user()->can('edit_calendar')) {
            $cal = Calendar::findOrFail($id);
        } else {
            $cal = Calendar::where('user_id',Auth::user()->id)->findOrFail($id);
        }
        return view('admin.calendaredit', compact( 'cal'));
    }
    public function updatecal(Request $request, $id)
    {
        $private = 1;
        if (Auth::user()->can('edit_calendar')) {$private=$request->isPrivate?1:null;}
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
        return redirect()->route('calendar');
    }
    public function delcal($id)
    {
        if (Auth::user()->can('edit_calendar')) {
        Calendar::find($id)->delete(); } else {
        Calendar::where('user_id',Auth::user()->id)->find($id)->delete();
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
    public function viewcal(Request $request) {
        if ($request->id) {
            $event = Calendar::findOrFail($request->id);
            return response()->json($event);
        }
        return response()->json([
            'state' => 'failed'
        ], 500);
    }


    public function update_settingsone(Request $request){
        
        $id = $request->input('id');
        $site_ar_title = $request->input('site_ar_title');
        $site_title = $request->input('site_title');
        $gtag = $request->input('gtag');
        $autoact = $request->input('autoact');
        $Platform_Name = $request->input('Platform_Name');
        $Platform_support_email = $request->input('Platform_support_email');
        $Country = $request->input('Country');
        $State = $request->input('State');
        $Default_Language = $request->input('Default_Language');
        $timezone = $request->input('timezone');
        $Currency = $request->input('Currency');
        

        $updateSittings = Settings::where('id', $id)->first();

        $updateSittings->ar_title = $site_ar_title;
        $updateSittings->title = $site_title;
        $updateSittings->url = $gtag;
        $request->input('autoact')=='on'?$updateSittings->set_order = 1:$updateSittings->set_order = null;
        $updateSittings->Platform_Name = $Platform_Name;
        $updateSittings->Platform_support_email = $Platform_support_email;
        $updateSittings->Country = $Country;
        $updateSittings->State = $State;
        $updateSittings->Default_Language = $Default_Language;
        $updateSittings->timezone = $timezone;
        $updateSittings->Currency = $Currency;
        $updateSittings->save();


        return redirect()->back()->with('message', 'StoredSuccessfully');
}
public function update_settinglogo(Request $request){
        
    $id = $request->input('id');

    $updateSittings = Settings::where('id', $id)->first();
   
    
    if ($request->hasFile('logo')) {
        $image = $request->file('logo');
    
        // الحصول على اسم الصورة الأصلي بدون الامتداد
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
    
        // تعديل اسم الصورة الأصلي - يمكنك تغيير الاسم بالطريقة التي تريدها
        // هنا نضيف "new_" كبادئة لاسم الصورة
        $newName = $updateSittings->Platform_Name; 
    
        // تحديد الامتداد الأصلي للصورة
        $extension = $image->getClientOriginalExtension();
    
        // تركيب الاسم الجديد مع الامتداد
        $logo = $newName . '.' . $extension;
        $destinationPath = public_path('images/header');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true); // إنشاء المجلد إذا لم يكن موجودًا
        }
        $image->move($destinationPath, $logo);
        // حفظ الصورة بالاسم الجديد
        // $image->storeAs('public/images/logo', $logo);
    } else {
        $logo = null; // إذا لم يتم رفع صورة، يمكنك التعامل معها هنا
    }

    if ($request->hasFile('header')) {
        $image = $request->file('header');
    
        // الحصول على اسم الصورة الأصلي بدون الامتداد
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
    
        // تعديل اسم الصورة الأصلي - يمكنك تغيير الاسم بالطريقة التي تريدها
        // هنا نضيف "new_" كبادئة لاسم الصورة
        $newName = $updateSittings->Platform_Name; 
    
        // تحديد الامتداد الأصلي للصورة
        $extension = $image->getClientOriginalExtension();
    
        // تركيب الاسم الجديد مع الامتداد
        $header = $newName . '.' . $extension;

        $destinationPath = public_path('images/header');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true); // إنشاء المجلد إذا لم يكن موجودًا
        }
        $image->move($destinationPath, $header);
       
    } else {
        $header = null; // إذا لم يتم رفع صورة، يمكنك التعامل معها هنا
    }

    if ($request->hasFile('footer')) {
        $image = $request->file('footer');
    
        // الحصول على اسم الصورة الأصلي بدون الامتداد
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
    
        // تعديل اسم الصورة الأصلي - يمكنك تغيير الاسم بالطريقة التي تريدها
        // هنا نضيف "new_" كبادئة لاسم الصورة
        $newName = $updateSittings->Platform_Name; 
    
        // تحديد الامتداد الأصلي للصورة
        $extension = $image->getClientOriginalExtension();
    
        // تركيب الاسم الجديد مع الامتداد
        $footer = $newName . '.' . $extension;
    
        $destinationPath = public_path('images/header');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true); // إنشاء المجلد إذا لم يكن موجودًا
        }
        $image->move($destinationPath, $footer);
        // حفظ الصورة بالاسم الجديد
        // $image->storeAs('public/images/footer', $footer);
    } else {
        $footer = null; // إذا لم يتم رفع صورة، يمكنك التعامل معها هنا
    }

    

    $updateSittings->Logo = $logo;
    $updateSittings->header = $header;
    $updateSittings->footer = $footer;
    $updateSittings->save();


    return redirect()->back()->with('message', 'StoredSuccessfully');
}

public function update_settingstwo(Request $request){
   
    $id = $request->input('id');
    $google = $request->input('google');
    

    $updateSittings = Settings::where('id', $id)->first();

    $updateSittings->google = $google;
    $updateSittings->save();


    return redirect()->back()->with('message', 'StoredSuccessfully');
}

public function update_settingsmail(Request $request){
        
   
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

public function update_settingspush(Request $request){
        
   
    $id = $request->input('id');
    $BroadCast = $request->input('BroadCast');
    $Secret_Key = $request->input('Secret_Key');
    $Client_ID = $request->input('Client_ID');
    $Cluster_name = $request->input('Cluster_name');
    
    $updateSittings = Settings::where('id', $id)->first();
    $updateSittings->BroadCast = $BroadCast;
    $updateSittings->Secret_Key = $Secret_Key;
    $updateSittings->Client_ID = $Client_ID;
    $updateSittings->Cluster_name = $Cluster_name;
    $updateSittings->save();


    return redirect()->back()->with('message', 'StoredSuccessfully');
}

public function update_settingsMeeting(Request $request){
       
    $id = $request->input('id');
    $Meeting = $request->input('Meeting');
    $GOOGLE_CLIENT_ID = $request->input('GOOGLE_CLIENT_ID');
    $GOOGLE_CLIENT_SECRET = $request->input('GOOGLE_CLIENT_SECRET');
    $GOOGLE_REDIRECT = $request->input('GOOGLE_REDIRECT');
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
    if($Meeting == 'Google'){
        
        $updateSittings->GOOGLE_CLIENT_ID = $GOOGLE_CLIENT_ID;
        $updateSittings->GOOGLE_CLIENT_SECRET = $GOOGLE_CLIENT_SECRET;
        $updateSittings->GOOGLE_REDIRECT = $GOOGLE_REDIRECT;
        $updateSittings->save();
    }
    if($Meeting == 'Microsoft'){
        $updateSittings->MICROSOFT_CLIENT_ID = $MICROSOFT_CLIENT_ID;
        $updateSittings->MICROSOFT_CLIENT_SECRET_VALUE = $MICROSOFT_CLIENT_SECRET_VALUE;
        $updateSittings->MICROSOFT_TENANT_ID = $MICROSOFT_TENANT_ID;
        $updateSittings->MICROSOFT_CLIENT_SECRET_ID = $MICROSOFT_CLIENT_SECRET_ID;
        $updateSittings->MICROSOFT_APP_EMAIL = $MICROSOFT_APP_EMAIL;
        $updateSittings->MICROSOFT_REDIRECT_URI = $MICROSOFT_REDIRECT_URI;
        $updateSittings->save();
    }
    if($Meeting == 'Zoom'){
        $updateSittings->ZOOM_CLIENT_ID = $ZOOM_CLIENT_ID;
        $updateSittings->ZOOM_CLIENT_SECRET = $ZOOM_CLIENT_SECRET;
        $updateSittings->ZOOM_ACCOUNT_ID = $ZOOM_ACCOUNT_ID;
        $updateSittings->save();
    }


    return redirect()->back()->with('message', 'StoredSuccessfully');
}
















}
