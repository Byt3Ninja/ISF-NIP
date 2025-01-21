<?php

namespace App\Http\Controllers;
use App\User;
use App\Cycle;
use App\Country;
use App\Currency;
use App\Schedule;
use App\UsersBio;
use App\Expertise;
use App\CyclesMentors;
use App\MentorshipTask;
use App\MentorshipRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ConfirmEmailUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UpdateClientProfileRequest;
use Intervention\Image\ImageManagerStatic as Image;
use Storage, Hash, DB, Exception, Session, URL, File;
use App\Http\Requests\UpdateClientProfilePasswordRequest;

class ClientProfileController extends Controller
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $Type
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        try
        {
            $client = Auth::user();
            $countries = Country::all();
            $expertises = Expertise::where('parent_id',null)->get();
            $Currencies = Currency::all();
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
            return view('admin/clients/editProfile', compact('client','Currencies', 'countries','expertises'));
        }
        catch (Exception $e)
        {
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function show(){
      $client = Auth::user();
      $mentors = CyclesMentors::where('user_id',  Auth::user()->id ?? 0)->get();

      if($client->hasRole('Admin')){
      $mentorshipTasks = MentorshipTask::where('mentor_id',Auth::user()->id)->where('status','pending')->get();
      }else{

      
        if ($client && $client->company) {
            $companyId = $client->company->id;
            $mentorshipTasks = MentorshipTask::where('company_id', $companyId)->get();
            
        }else{
            $mentorshipTasks = MentorshipTask::where('mentor_id', Auth::user()->id)->get();
        }
    }
      
      $mentrequests =  MentorshipRequest::where('mentor_id', Auth::user()->id);

        $mentrequests_count = $mentrequests->count();
        $mentrequests_period = $mentrequests->sum('period')/60;


        $average_rating = MentorshipRequest::where('mentor_id', Auth::user()->id)
    ->where('approved', 'Yes')
    ->whereNotNull('rating') // تأكد أن عمود التقييم يحتوي على قيم
    ->avg('rating'); 
   

      return view('admin/clients/profile', compact('client','mentors','mentorshipTasks','mentrequests_count','mentrequests_period','average_rating'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */

    public function Updatebio(Request $request)
    {
       
            $user = Auth::user();
            // Mentor Bio section
            if ($request->mentor_section){
                $data =[
                    'bio_ar'=>$request->bio_ar,
                    'bio_en'=>$request->bio_en,
                    'linkedin'=>$request->linkedin,
                    'entrepreneurial_experience'=>$request->entrepreneurial_experience,
                    'profile_privacy'=>$request->profile_privacy,
                    'mentorship_approach'=>json_encode($request->mentorship_approach),
                ];
                if ($request->entrepreneurial_experience == 1){
                    $data['entrepreneurial_experience_brief']=$request->entrepreneurial_experience_brief;
                }else{
                    $data['entrepreneurial_experience_brief']=0;
                }
                $user->bio()->updateOrCreate(['user_id'=>$user->id],$data);
                $user->mentorExpertises()->sync($request->expertises);
            }

            return redirect()->route('my_profile')->with( ['msg' => 'StoredSuccessfully', 'class' => 'success'] );
    }
    public function update(UpdateClientProfileRequest $request)
    {           
        
        try{
            $first_name_ar = substr($request->first_name_ar, 0, 250);
            $last_name_ar = substr($request->last_name_ar, 0, 250);
            
            $user = Auth::user();

            // // Mentor Bio section
            // if ($request->mentor_section){
            //     $data =[
            //         'bio_ar'=>$request->bio_ar,
            //         'bio_en'=>$request->bio_en,
            //         'linkedIn'=>$request->linkedin,
            //         'entrepreneurial_experience'=>$request->entrepreneurial_experience,
            //         'profile_privacy'=>$request->profile_privacy,
            //         'mentorship_approach'=>json_encode($request->mentorship_approach),
            //     ];
            //     if ($request->entrepreneurial_experience){
            //         $data['entrepreneurial_experience_brief']=$request->entrepreneurial_experience_brief;
            //     }
            //     $user->bio()->updateOrCreate(['user_id'=>$user->id],$data);
            //     $user->mentorExpertises()->sync($request->expertises);
            // }
            

            if ($request->hasFile('img_url')) {

                $getemail = User::where('id', $user->id)->first();

                if($request->email == $getemail->email){
                    //store the image
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
                //resize
                Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 200, function($con) {$con->aspectRatio();})->save();
                //update
                
                $result = $user->update($request->except(['img_url','password','email','first_name_ar','last_name_ar']) + ['img_url' => $img_url]);
                $user->first_name_ar = $first_name_ar;
                    $user->last_name_ar = $last_name_ar;
                    $user->save();
            }else{
                    // if email changed send email verification
                    //store the image
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
                //resize
                Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 200, function($con) {$con->aspectRatio();})->save();
                //update
                $result = $user->update($request->except(['img_url','password','email','first_name_ar','last_name_ar']) + ['img_url' => $img_url]);
                $user->first_name_ar = $first_name_ar;
                $user->last_name_ar = $last_name_ar;
              
                    $user->new_email = $request->email;
                    $user->save();
                     // إرسال رابط التحقق
                    $verificationUrl = route('email.confirm', ['token' => encrypt($user->new_email)]);

                    Mail::to($user->new_email)->send(new ConfirmEmailUpdate($verificationUrl));
                    return redirect()->route('my_profile')->with( ['msg' => 'UpdateEmailSent', 'class' => 'success'] );
                }

                


            }else{
                $getemail = User::where('id', $user->id)->first();

                if($request->email == $getemail->email){
                    
                    $result = $user->update($request->except(['password','email','first_name_ar','last_name_ar']));
                    $user->first_name_ar = $first_name_ar;
                    $user->last_name_ar = $last_name_ar;
                    $user->save();
                }else{
                    $result = $user->update($request->except(['password','email','first_name_ar','last_name_ar']));
                    $user->first_name_ar = $first_name_ar;
                    $user->last_name_ar = $last_name_ar;
              
                    $user->new_email = $request->email;
                    $user->save();
                     // إرسال رابط التحقق
                    $verificationUrl = route('email.confirm', ['token' => encrypt($user->new_email)]);

                    Mail::to($user->new_email)->send(new ConfirmEmailUpdate($verificationUrl));
                    return redirect()->route('my_profile')->with( ['msg' => 'UpdateEmailSent', 'class' => 'success'] );
                }
            
                
            }
            
            if($result)
                return redirect()->route('my_profile')->with( ['msg' => 'StoredSuccessfully', 'class' => 'success'] );
            else
                return back()->withInput()->with( ['msg' => 'FailedStore', 'class' => 'danger'] );
        }catch (Exception $e) {
            //log what happend
            dd($e);
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

        public function confirmEmailUpdate($token)
    {
        try {
            $newEmail = decrypt($token);
          
        } catch (\Exception $e) {
           
            return redirect()->route('my_profile')->with(['msg' => 'WrongLink', 'class' => 'danger']);

        }

        $user = auth()->user();

        // تحقق من وجود البريد الجديد
        if ($user->new_email != $newEmail) {
            return redirect()->route('my_profile')->with(['msg' => 'WrongLink', 'class' => 'danger']);
        }

        // تحديث البريد الإلكتروني
        $user->email = $newEmail;
        $user->new_email = null;
        $user->save();

        return redirect()->route('my_profile')->with(['msg' => 'EmailUpdatedSuccessfully', 'class' => 'success']);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(UpdateClientProfilePasswordRequest $request)
    {
        try{
            $user = Auth::user();
            $password = Hash::make($request->password);
            $result = $user->where('id', $user->id)
                            ->update(['password' => $password]);
            if($result)
                return redirect()->route('my_profile')->with( ['msg' => 'StoredSuccessfully', 'class' => 'success'] );
            else
                return back()->withInput()->with( ['msg' => 'FailedStore', 'class' => 'danger'] );
        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function showMintorprofile($id){

        
        $client = User::find($id);
        if(!empty($client)){
        // $exp = Expertise::where('parent_id',null)->get();
        // $expertises = Expertise::where('parent_id',null)->get();
        $mentrequests =  MentorshipRequest::where('mentor_id', $id);

        $mentrequests_count = $mentrequests->count();
        $mentrequests_period = $mentrequests->sum('period')/60;


        $average_rating = MentorshipRequest::where('mentor_id', $id)
    ->where('approved', 'Yes')
    ->whereNotNull('rating') // تأكد أن عمود التقييم يحتوي على قيم
    ->avg('rating'); 


         
        $mentors = User::find($id);
        if ($mentors) {
            $expertises = $mentors->mentorExpertises; // جلب جميع الخبرات المرتبطة بهذا الموجه
        } else {
            // الموجه غير موجود
            $expertises = [];
        }
        $mentors = CyclesMentors::where('user_id', $id ?? 0)->get();



        $order = CyclesMentors::where('cycle_id', Auth::user()->company->cycle ?? 0)->where('user_id', $id ?? 0)->first();

        
        $mentorshipTasks = MentorshipTask::where('mentor_id',$id)->where('status','pending')->get();
       

            return view('admin/clients/UserProfile', compact('client',
            'expertises',
            'mentors',
            'order',
            'mentorshipTasks',
            'mentrequests_count',
            'mentrequests_period',
            'average_rating'));

    }else{
        return redirect()->route('my_profile')->with( ['msg' => 'FailedStore', 'class' => 'danger'] );
    }
    }

    public function showMintors(Request $request){

        $search = $request->input('search');
        $rateSearch = $request->input('rateSearch');
        $ExpertiesSearch = $request->input('ExpertiesSearch');

        
        
    $l = app()->getLocale();
    
    // إعداد الأعمدة الصحيحة بناءً على اللغة
    $firstNameColumn = $l == 'ar' ? 'first_name_ar' : 'first_name_en';
    $lastNameColumn = $l == 'ar' ? 'last_name_ar' : 'last_name_en';

    // البحث في الموجهين
    $mentors = User::role('mentor')
        ->whereRaw("CONCAT($firstNameColumn, ' ', $lastNameColumn) LIKE ?", ["%{$search}%"]);

    if ($rateSearch > 0) {
        $mentors = $mentors->whereHas('mentorshipRequests', function ($q) use ($rateSearch) {
            $q->where(function ($q2) {
                $q2->where('approved', 'Yes')
                   ->orWhereNull('approved');
            })->havingRaw('AVG(rating) >= ?', [$rateSearch]);
        });
    }
    
    if (!empty($ExpertiesSearch)) {
        // جلب الخبرة المرتبطة بعنوان الخبرة المدخل
        $expertiesMentor = Expertise::where('title', '=', $ExpertiesSearch)->first();

        if ($expertiesMentor) { // تأكد من وجود الخبرة
            $expid = $expertiesMentor->id;

            // تصفية الموجهين بناءً على الخبرة
            $mentors = $mentors->whereHas('mentorExpertises', function ($query) use ($expid) {
                $query->where('expertise_id', $expid); // استخدم where بدلاً من whereIn لأنه لدينا id واحد
            });
        }
    }


    // جلب النتائج
    $mentors = $mentors->get();
        // $mentors = User::role('mentor')->get();
        
        

            return view('admin/clients/Mentors', compact('mentors'));


    }



}
