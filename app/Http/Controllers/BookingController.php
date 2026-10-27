<?php

namespace App\Http\Controllers;

use App\Inclusive;
use App\Room;
use App\Room_booking;
use App\Rooms_inclusive;
use App\Rooms_img;
use App\User;
use Carbon\Carbon;
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

class BookingController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    public function index()
    {
        $rooms = Room::with('User')->get();
        return view('admin/rooms/booking/index', compact('rooms'));
    }

    public function review()
    {
        $booking_reviews = room_booking::where('fromDate','>=',Carbon::now())->where('approved','=','Reviewing')->with('room')->with('client')->with('User')->get();
        return view('admin/rooms/booking/bookingreview', compact('booking_reviews'));
    }

    public function upcomingApprovedReview()
    {
        $booking_reviews = room_booking::where('fromDate','>=',Carbon::now())->where('approved','=','approved')->with('room')->with('client')->with('User')->get();
        return view('admin/rooms/booking/bookingreview', compact('booking_reviews'));
    }

    public function upcomingBusyReview()
    {
        $booking_reviews = room_booking::where('fromDate','>=',Carbon::now())->where('approved','=','Busy')->with('room')->with('client')->with('User')->get();
        return view('admin/rooms/booking/bookingreview', compact('booking_reviews'));
    }

    public function oldReview()
    {
        $booking_reviews = room_booking::where('fromDate','<=',Carbon::now())->with('room')->with('client')->with('User')->get();
        return view('admin/rooms/booking/oldReview', compact('booking_reviews'));
    }
    public function create()
    {
        $rooms = Room::with('User')->get();
        return view('admin/rooms/booking/index', compact('rooms'));
    }

    public function store(request $request)
    {
        $user = Auth()->user()->id;
        // C4: the forced fields go on the left of `+` (the left operand wins) and except() takes one
        // array -- the old call only dropped user_id, so a posted `approved` let a booker self-approve.
        $room = Room_booking::create(
            ['user_id' => $user,
            'approved' => 'Reviewing',
            'user_added' => null] + $request->except(['user_id', 'approved', 'user_added'])
        );
        if($room) { return redirect()->back()->with( ['msg' => 'StoredSuccessfully'] ); }
        return redirect()->back()->with( ['msg' => 'admin.FailedStore'] );
    }

    public function show($id)
    {
        $room = Room::findOrFail($id);
        $roomImages= Rooms_img::where('room_id',$id)->get();
        $roomInclusive = Rooms_inclusive::where('room_id','=',$id)->get();
        return view('admin.rooms.booking.create')
            ->with('room', $room)
            ->with('roomInclusive',$roomInclusive)
            ->with('roomImages',$roomImages);
    }
    public function edit($id)
    {
        //
    }

    public function update($id, request $request)
    {
        //
    }

    public function approve($id,$action)
    {
        $user = Auth()->user()->id;
        $getBooking = Room_booking::findOrFail($id);
        $getBooking->approved = ( $action == '1' ) ? 'Approved' : 'Busy';
        $getBooking->user_added=$user;
        $getBooking->save();
        return redirect()->back()->with('msg', 'You successfully update the Request.');
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
        if( $image ) { return redirect()->back()->with( ['msg' => 'DeleteSuccessfully'] ); }
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
}
