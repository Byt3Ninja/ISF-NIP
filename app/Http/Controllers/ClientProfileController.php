<?php

namespace App\Http\Controllers;
use App\Country;
use App\Cycle;
use App\Expertise;
use App\Http\Requests\UpdateClientProfileRequest;
use App\Http\Requests\UpdateClientProfilePasswordRequest;
use App\User;
use App\UsersBio;
use Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Hash;
use DB;
use Auth;
use Log;
use Exception;
use Session;
use App\CyclesMentors;
use App\Schedule;
use App\MentorshipRequest;
use App\MentorshipTask;

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
    public function edit(){
//        dd(auth()->id());
        try{
            $client = Auth::user();
            $countries = Country::all();
            $expertises = Expertise::where('parent_id',null)->get();
            return view('admin/clients/editProfile', compact('client','countries','expertises'));
        }catch (Exception $e){
            //log what happend
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
    public function update(UpdateClientProfileRequest $request)
    {
        try{
            $user = Auth::user();

            // Mentor Bio section
            if ($request->mentor_section){
                $data =[
                    'bio_ar'=>$request->bio_ar,
                    'bio_en'=>$request->bio_en,
                    'linkedIn'=>$request->linkedin,
                    'entrepreneurial_experience'=>$request->entrepreneurial_experience,
                    'profile_privacy'=>$request->profile_privacy,
                    'mentorship_approach'=>json_encode($request->mentorship_approach),
                ];
                if ($request->entrepreneurial_experience){
                    $data['entrepreneurial_experience_brief']=$request->entrepreneurial_experience_brief;
                }
                $user->bio()->updateOrCreate(['user_id'=>$user->id],$data);
                $user->mentorExpertises()->sync($request->expertises);
            }

            if ($request->hasFile('img_url')) {
                //store the image
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
                //resize
                Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 200, function($con) {$con->aspectRatio();})->save();
                //update
                $result = $user->update($request->except(['img_url','password']) + ['img_url' => $img_url]);


            }else{
                $result = $user->update($request->except(['password']));
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


    }

    public function showMintors(){

      
        $mentors = User::role('mentor')->get();
        
       

            return view('admin/clients/Mentors', compact('mentors'));


    }



}
