<?php

namespace App\Http\Controllers;
use App\Country;
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
      $mentors = CyclesMentors::where('user_id', Auth::user()->id ?? 0)->get();

      return view('admin/clients/profile', compact('client','mentors'));
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

    public function showMintor($id){

        $client = User::find($id);
        // $exp = Expertise::where('parent_id',null)->get();
        // $expertises = Expertise::where('parent_id',null)->get();
        
        $mentors = User::find($id);
        if ($mentors) {
            $expertises = $mentors->mentorExpertises; // جلب جميع الخبرات المرتبطة بهذا الموجه
        } else {
            // الموجه غير موجود
            $expertises = [];
        }
        $mentors = CyclesMentors::where('user_id', $id ?? 0)->get();

        $order = CyclesMentors::where('cycle_id', Auth::user()->company->cycle ?? 0)->where('user_id', $id ?? 0)->first();


        $my = Schedule::where('active',1)->where('user_id', $id)->get();
        $sessions = MentorshipRequest::where('mentor_id',$id)->where('zoom_date','>=',date("Y-m-d 00:00:00"))->where('approved','Yes')
            ->get(['zoom_date as dfrom','period AS tfrom',DB::raw("(id + 1000000) AS id, 'Session' AS type, 'Mentorship session' AS notes")]);
        $my = $my->merge($sessions);
        


            return view('admin/clients/Mentorprofile', compact('client','expertises', 'mentors', 'order', 'my'));


    }





}
