<?php

namespace App\Http\Controllers;

use App\Inclusive;
use App\Room;
use App\Rooms_inclusive;
use App\Rooms_img;
use App\User;

use Illuminate\Http\Request;
use \Spatie\Permission\Models\Role as Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;

use DB;
use Log;
use Exception;
use Session;
use Hash;
use Illuminate\Support\Facades\Auth;
use App\Services\FileManagerService;

class RoomsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		$rooms = Room::with('User')->get();
		return view('admin/rooms/index', compact('rooms'));
	}

	public function create()
	{
		$inclusives = Inclusive::all();
		return view('admin/rooms/create', compact('inclusives'));
	}

	public function store(request $request)
	{
		$user = Auth()->user()->id;
		$room = Room::create($request->except(['user_added']) + ['user_added' => $user])->id;
		if($room)
		{
			if($request->hasFile('image'))
			{
				$files = $request->file('image');
				foreach($files as $file){
					//get the path of the stores image
					$file = app(FileManagerService::class)->upload($file, 'rooms', 'public_uploads', 'image');
					Rooms_img::create([
						'room_id' => $room,
						'img_url' => $file,
						'user_added' => $user,
					]);
				}
			}
			$inclusives = $request->input('inclusives');
			if ($inclusives <> null) {
				foreach ($inclusives as $inclusive) {
					Rooms_inclusive::create(
						[
							'room_id' => $room,
							'inclusive_id' => $inclusive,
							'user_added' => $user,
						]
					);
				}
			}

			return redirect()->back()->with( ['msg' => 'StoredSuccessfully'] );
		}
		return redirect()->back()->with( ['msg' => 'admin.FailedStore'] );
	}

	public function show()
	{
		//
	}

	public function edit($id)
	{
		$room = Room::findOrFail($id);
		$inclusives = Inclusive::all();
		$roomInclusive = Rooms_inclusive::where('room_id','=',$id)->get();
		$count=0;
		foreach ($inclusives as $inclusive){
			$found =Rooms_inclusive::where('inclusive_id','=',$inclusive->id)->where('room_id','=',$id)->first();
			if ($found){
				$inclusives->pull($count);
			}
			$count++;
		}
		return view('admin.rooms.edit')
			->with('room', $room)
			->with('roomInclusive',$roomInclusive)
			->with('inclusives',$inclusives);
	}

	public function update($id, request $request)
	{
		$user = Auth()->user()->id;
		$getRoom = Room::findOrfail($id);
		$published=$request->input('published');
		$room = $getRoom->update($request->except(['user_added','published']) + ['user_added' => $user,'published' => $published]);
		if($room)
		{
			if($request->hasFile('image'))
			{
				$files = $request->file('image');
				foreach($files as $file){
					 //get the path of the stores image
					$file = app(FileManagerService::class)->upload($file, 'rooms', 'public_uploads', 'image');
					Rooms_img::create([
						'room_id' => $id,
						'img_url' => $file,
						'user_added' => $user,
					]);
				}
			}
			if ($request->input('inclusives') <> null){
			Rooms_inclusive::where('room_id','=',$id)->delete();
			$inclusives = $request->input('inclusives');
			if ($inclusives <> null) {
				foreach ($inclusives as $inclusive) {
					Rooms_inclusive::create(
						[
							'room_id' => $room,
							'inclusive_id' => $inclusive,
							'user_added' => $user,
						]
					);
				}
			}
			}
			return redirect()->back()->with( ['msg' => 'StoredSuccessfully'] );
		}
		return redirect()->back()->with( ['msg' => 'admin.FailedStore'] );
	}

	public function roomImages($id,Request $request)
	{
		$roomImages= Rooms_img::where('room_id',$id)->get();
		return view('admin/rooms/images', compact('roomImages','id'));
	}

	public function deleteImages($id)
	{
		$image = Rooms_img::find($id);
		$image->delete();
		if ($image) { return redirect()->back()->with( ['msg' => 'DeleteSuccessfully'] ); }
		return redirect()->back()->with( ['msg' => 'admin.FailedDelete'] );
	}

	public function destroy(User $user)
	{
		if ($user->id==1){return response()->json(['state' => 'failed'], 500);}
		$user->roles()->detach();
		$user->delete();
		$user->company()->delete();
		return response()->json(['state' => 'success'], 200);
	}

	public function showrooms()
	{
		$rooms = Room::with('User')->get();
		return view('admin/rooms/index', compact('rooms'));
	}
}
