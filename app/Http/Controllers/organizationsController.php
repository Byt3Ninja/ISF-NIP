<?php

namespace App\Http\Controllers;
use App\Http\Requests\UpdateClientProfileRequest;
use App\Http\Requests\UpdateClientProfilePasswordRequest;
use App\User;
use App\Role as Roles;
use App\Organization;
use Storage;
use \Spatie\Permission\Models\Role as Role;
use Intervention\Image\ImageManagerStatic as Image;
use Hash;
use DB;
use Auth;
use Log;
use Exception;
use Session;
use Illuminate\Http\Request;

class organizationsController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() { $this->middleware('auth'); }

	public function index()
	{
		$organizations = Organization::all();
		return view('admin.organizations.index', compact('organizations'));
	}

	public function create()
	{
//		$roles = Role::all();
		return view('admin.organizations.create');
	}

	public function store(Request $request)
	{
		$this->validate($request, [
			'first_name_en' => ['required', 'string', 'max:255'],
			'last_name_en' => ['required', 'string', 'max:255'],
			'phone' => ['required'],
			'email' => 'email|required|unique:users,email',
			'password' => ['required', 'string', 'min:8', 'confirmed'],
			'name_ar' => ['required', 'string', 'max:255'],
			'name_en' => ['required', 'string', 'max:255'],
		]);
		$role = Roles::where('name', 'organizationManager')->first();
		if( empty( $role ) )
		{
			$role = new Roles;
			$role->name = 'organizationManager';
			$role->guard_name = 'web';
			$role->save();
		}
		$user = User::create($request->except(['_token', 'name_en', 'name_ar', 'password_confirmation']));
		$user->assignRole( $role->id );
		
		$organization = new Organization;
		$organization->name_ar = $request->name_ar;
		$organization->name_en = $request->name_en;
		$organization->status = intval( $request->status ) == 1 || $request->status === true ? 1 : 0;
		$organization->user_id = $user->id;
		$organization->color = $request->color;
        
            //store the image
			if($request->input('img_url') != null){
				$img_url = $request->input('img_url');
			}else{
				$img_url = '/images/background/logo.png';
			}
            //update
             $organization->img_url= $img_url;
        
		$organization->save();

		$user->organization_id = $organization->id;
		$user->password = Hash::make( $request->password );
		$user->email_verified_at = date('Y-m-d H:i:s');
        $user->HaveCompany=0;
		$user->save();
		return redirect()->route('organizations.index')->with( ['msg' => 'StoredSuccessfully'] );
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Organization $organization)
	{
		return view('admin.organizations.edit', compact('organization'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Type  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function update($id, Request $request)
	{
		$organization = Organization::with(['user'])->find( $id );
		$except = ['_token', '_method', 'name_en', 'name_ar', 'status', 'password_confirmation'];
		if( $request->has('password') && isset( $request->password ) && trim( $request->password ) ) {  } else { $except[] = 'password'; }
		$this->validate($request, [
			'first_name_en' => ['required', 'string', 'max:255'],
			'last_name_en' => ['required', 'string', 'max:255'],
			'phone' => ['required'],
			'email' => 'email|required|unique:users,email,' . $organization->user_id,
			'password' => ['nullable', 'string', 'min:8', 'confirmed'],
			'name_ar' => ['required', 'string', 'max:255'],
			'name_en' => ['required', 'string', 'max:255'],
		]);

		if( !in_array('password', $except) )
		{
			$request->merge(['password' => Hash::make( $request->password )]);
		}
		$organization->name_ar = $request->name_ar;
		$organization->name_en = $request->name_en;
		$organization->status = intval( $request->status ) == 1 || $request->status === true ? 1 : 0;
		$organization->color = $request->color;
        

            //store the image
			if($request->input('img_url') != null){
				$img_url = $request->input('img_url');
			}else{
				$img_url = $organization->img_url;
			}
            //update
            $organization->img_url= $img_url;
        
		$organization->save();
		$user = $organization->user;
		$user->update( $request->except( $except ) );
		return redirect()->route('organizations.index')->with( ['msg' => 'StoredSuccessfully'] );
	}

	public function destroy( $id )
	{
		$organization = Organization::with(['user'])->find( $id );
		if( !empty( $organization ) )
		{
			$user = $organization->user;
			if( !empty( $user ) ) { $user->delete(); }
			$organization->delete();
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

	public function GetLFM( $path = null , $file = null )
	{
		
		try {
			 $instance = Storage::disk('UsersLFMPublic');

			 $fullPath = '/' . $path . '/' . $file;
			//  $check_file = $instance->missing( $fullPath);
			if( $instance->directoryExists( $fullPath ) ) { return response()->view('errors.404')->setStatusCode( 404 ); }
			$file = $instance->response( $fullPath);
			
			// $mimeType = $instance->mimeType($file);
			// if( $mimeType == 'image/svg' ) { $mimeType = 'image/svg+xml'; }
			// $file->headers->set('Content-Type', $mimeType);
			
			return $file;
		} catch( \Throwable $th ) { return response()->view('errors.404')->setStatusCode( 404 ); }
	}
}
