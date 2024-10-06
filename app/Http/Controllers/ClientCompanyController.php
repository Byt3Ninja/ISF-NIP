<?php

namespace App\Http\Controllers;
use App\Country;
use App\Events\WebMessage;
use App\FundingRound;
use App\Sector;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateClientCompanyRequest;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\State;
use App\City;
use App\Company;
use App\Like;
use App\Settings;
use App\SustainableIcon;
use DB;
use Auth;
use Log;
use Exception;
use Session;
use Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ClientCompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('auth');
    }
    public function show($id)
    {
        try{
            $company = Company::with('state')
                                ->with('city')
                                ->with('user')
                                ->findorfail($id);

                                $sustainableDevelopment = json_decode($company->Sustainable_development, true);

                                $icons = SustainableIcon::whereIn('title', $sustainableDevelopment)->get();

                                // استرجاع الأيقونات المناسبة
                                // $icons = SustainableIcon::where('title', $sustainableDevelopment)->get();
                                
                                
            return view('admin/clients/company_profile', compact('company','icons'));
        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }
    /**
     * Show the form for editing/Createing the specified resource.
     *
     * @param  \App\Company  $Type
     * @return \Illuminate\Http\Response
     */
    public function editOrCreate(){
        try{
            $client = User::where('HaveCompany',1)->with('company')
                ->with('company.city')
                ->find(Auth::id());
            $countries = Country::all();
            $states = State::all();
            $cities = City::all();
            $Setting = Settings::where('id', 1)->first();
            $sectors = Sector::whereNull('parent_id')->get();
            $funding_rounds=auth()->user()->company?auth()->user()->company->fundingRounds:[];
            return view('admin/clients/company', compact(
                'client', 'states', 'cities','countries','sectors','funding_rounds','Setting'
            ));
        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            dd($e);
            abort(500);
        }
    }
    /**
     * Update/Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrStore(StoreUpdateClientCompanyRequest $request)
    {
        try{
            $user = Auth::user();
            $companyId = $user->company_id;
            $data = $request->all();
            $data['institutions'] = json_encode($request->institutions);
            if ($request->hasFile('img_url')) {
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
                //resize
                Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 200, function($con) {$con->aspectRatio();})->save();
                $data['logo' ]= $img_url;
            }
            if ($request->hasFile('business_model')) {
                $business_model = $request->file('business_model');
                $business_model = $business_model->store('users', ['disk' => 'public_uploads']);
                $data['business_model' ]= $business_model;
            }
            if ($request->hasFile('business_plan')) {
                $business_plan = $request->file('business_plan');
                $business_plan = $business_plan->store('users', ['disk' => 'public_uploads']);
                $data['business_plan' ]= $business_plan;
            }
            if ($request->hasFile('pitch_deck')) {
                $pitch_deck = $request->file('pitch_deck');
                $pitch_deck = $pitch_deck->store('users', ['disk' => 'public_uploads']);
                $data['pitch_deck' ]= $pitch_deck;
            }
            if ($request->hasFile('financial_plan')) {
                $financial_plan = $request->file('financial_plan');
                $financial_plan = $financial_plan->store('users', ['disk' => 'public_uploads']);
                $data['financial_plan' ]= $financial_plan;
            }
            $result = Company::updateOrCreate(['id' => $companyId], $data);

                $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }
            if($result) {
                event(new WebMessage($msgtype, $user->first_name_en));
                return redirect()->route('my_company')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
            }
            else {
                return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
                }
        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }
     /**
     * Update/Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function likeService(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|numeric',
                'service_id' => 'required|numeric',
                'current_state' => 'required',
                'rate' => 'numeric',
            ]);
            if ($validator->fails())
                return $this->validationErrorsResponse($validator->errors());
            $user = Auth::user()->id;
            $current_state = $request->current_state;
            $company_id = $request->company_id;
            $service_id = $request->service_id;
            if($current_state == 'unliked'){ //Like
                $result = Like::updateOrCreate(
                        $request->only(['company_id','service_id']) + ['user_id' => $user],
                        $request->all() + ['user_id' => $user]
                    );
            }elseif($current_state == 'liked'){ //Unlike
                Like::where('company_id', $company_id)
                        ->where('service_id', $service_id)
                        ->where('user_id', $user)
                        ->delete();
            }
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been Deleted successfully";
            $dataContent = ['row_id'=> $result->id];
            if($result)
                return $this->returnApiResult($code, $status, $message, $dataContent);
            else
                return $this->unknownErrorHappenedMsg();
        }catch (Exception $e){ dd($e);
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }
}
