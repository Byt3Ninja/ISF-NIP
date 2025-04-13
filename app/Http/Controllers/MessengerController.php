<?php

namespace App\Http\Controllers;

use App\User;
use App\Cycle;
use App\Company;
use App\Messages;
use App\Messenger;
use App\MessageDraft;
use App\Organization;
use App\CyclesMentors;
use App\Events\WebMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use stdClass;

class MessengerController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::user()->id;
        $messages = $this->messenger_query(null, $request);
        //  dd( $messages );
        
        // Fetch received messages
        $receivedMessages = Messenger::leftJoin('users', 'sender', '=', 'users.id')
            ->where('receiver', $userId)
            ->whereNull('reply_id')
            ->orderBy('created_at', 'DESC')
            ->get([
                'messenger.id',
                'first_name_en',
                'last_name_en',
                'first_name_ar',
                'last_name_ar',
                'subject',
                'message',
                'read',
                'starred',
                'messenger.created_at',
                'messenger.sender'
            ]);
            
            // الآن نجيب الردود المرتبطة بكل رسالة
            $messageIds = $receivedMessages->pluck('id')->toArray();

            $replies = DB::table('messenger')
                ->whereIn('reply_id', $messageIds)
                ->get();

            // تجميع الردود تحت كل رسالة
            $groupedReplies = $replies->groupBy('reply_id');

            // دمج الردود مع الرسائل
            $receivedMessages = $receivedMessages->map(function ($message) use ($groupedReplies) {
                $message->replies = $groupedReplies[$message->id] ?? collect();
                return $message;
            });
            

        // Count all messages
        $receivedMessagesCount = Messenger::where('receiver', $userId)
            // ->where('read', 0)
            ->count();



        // Fetch sent messages
        // $sentMessages = Messenger::where('messenger.sender', $userId)
        //     ->join('users', 'messenger.receiver', '=', 'users.id')
        //     ->orderBy('messenger.created_at', 'DESC')
        //     ->get([
        //         'messenger.id',
        //         'users.first_name_en',
        //         'users.last_name_en',
        //         'users.first_name_ar',
        //         'users.last_name_ar',
        //         'messenger.subject',
        //         'messenger.message',
        //         'messenger.read',
        //         'messenger.starred',
        //         'messenger.receiver',
        //         'messenger.created_at'
        //     ]);

        // Fetch additional data if needed
        $users = $mentors = $cycles = $organizations = null;
        if (Auth::user()->hasAnyRole('Admin|OrganizationManager')) {
            $cycles = Cycle::select('id', 'title')->get();
            $users = Company::get();
            $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
            $organizations = Organization::all();
        } elseif (Auth::user()->hasRole('Mentor')) {
            $cycles = CyclesMentors::where('user_id', $userId)->get(['cycle_id'])->toArray();
            $users = Company::whereIn('cycle', array_values($cycles))->get();
        } elseif (Auth::user()->company) {
            if (Auth::user()->company->approved) {
                $users = Company::where('cycle', Auth::user()->company->cycle)
                    ->where('id', '!=', Auth::user()->company->id)
                    ->whereHas('user', function ($query) {
                        $query->role('Registered');
                    })->get();

                $mentors = User::join('cycles_mentors', 'users.id', 'user_id')
                    ->where('cycle_id', Auth::user()->company->cycle)
                    ->get(['users.id', 'first_name_ar', 'first_name_en', 'last_name_ar', 'last_name_en']);
            }
        }
       
        // $drafts = DB::table('message_drafts')->where('sender', $userId)->get();

        $drafts = MessageDraft::leftJoin('users', 'sender', '=', 'users.id')
            ->where('sender', $userId)
            ->orderBy('created_at', 'DESC')
            ->get([
                'message_drafts.id',
                'first_name_en',
                'last_name_en',
                'first_name_ar',
                'last_name_ar',
                'subject',
                'message',
                'message_drafts.created_at',
                'message_drafts.sender'
            ]);

    //     $trashedMessages = DB::table('messenger')
    // ->whereNotNull('deleted_at')
    // ->where(function($query) {
    //     $query->where('sender', Auth::id())
    //           ->orWhere('receiver', Auth::id());
    // })
    // ->get();

    $trashedMessages = DB::table('messenger')
    ->whereNotNull('messenger.deleted_at')
    ->where(function($query) {
        $query->where('sender', Auth::id())
              ->orWhere('receiver', Auth::id());
    })
    ->join('users', function($join) {
        $join->on('users.id', '=', DB::raw("
            CASE
                WHEN messenger.sender = " . Auth::id() . " THEN messenger.receiver
                ELSE messenger.sender
            END
        "));
    })
    ->orderBy('messenger.created_at', 'DESC')
    ->select(
        'messenger.*',
        'users.first_name_en',
        'users.last_name_en',
        'users.first_name_ar',
        'users.last_name_ar'
    )
    ->get()
    ->map(function ($item) {
        return (array) $item; // ← نحوله إلى مصفوفة
    });

    // $starred = DB::table('messenger')
    // ->where('starred', 1)
    // ->where(function($query) {
    //     $query->where('sender', Auth::id())
    //           ->orWhere('receiver', Auth::id());
    // })
    // ->get();

    $starred = Messenger::where(function($query) use ($userId) {
                $query->where('sender', $userId)
                    ->orWhere('receiver', $userId);
                    })->where('starred', 1)
            ->join('users', 'messenger.receiver', '=', 'users.id')
            ->orderBy('messenger.created_at', 'DESC')
            ->get([
                'messenger.id',
                'users.first_name_en',
                'users.last_name_en',
                'users.first_name_ar',
                'users.last_name_ar',
                'messenger.subject',
                'messenger.message',
                'messenger.read',
                'messenger.starred',
                'messenger.receiver',
                'messenger.sender',
                'messenger.created_at'
            ]);

            $sentMessages = Messenger::where('messenger.sender', $userId)
            ->whereNull('reply_id')
            ->join('users', 'messenger.receiver', '=', 'users.id')
            ->orderBy('messenger.created_at', 'DESC')
            ->get([
                'messenger.id',
                'users.first_name_en',
                'users.last_name_en',
                'users.first_name_ar',
                'users.last_name_ar',
                'messenger.subject',
                'messenger.message',
                'messenger.read',
                'messenger.starred',
                'messenger.receiver',
                'messenger.sender',
                'messenger.created_at'
            ]);
        
            // الآن نجيب الردود المرتبطة بكل رسالة
            $messageIdssent = $sentMessages->pluck('id')->toArray();

            $replies = DB::table('messenger')
                ->whereIn('reply_id', $messageIdssent)
                ->get();

            // تجميع الردود تحت كل رسالة
            $groupedReplies = $replies->groupBy('reply_id');

            // دمج الردود مع الرسائل
            $sentMessages = $sentMessages->map(function ($message) use ($groupedReplies) {
                $message->replies = $groupedReplies[$message->id] ?? collect();
                return $message;
            });
        


        // Return a single view with all data
        return view('admin.clients.messages.index', compact(
            'messages',
        'receivedMessages', 
        'receivedMessagesCount', 
        'sentMessages', 
        'users', 
        'mentors', 
        'cycles', 
        'organizations', 
        'drafts',
        'trashedMessages',
        'starred',
        ));
    }

    public function show($id, Request $request)
    {
        $message = $this->messenger_query($id, $request);
        dd( $message );
    }

    // public function index()
    // {
    //     $receivedMessages = Messenger::leftJoin('users', 'sender', '=', 'users.id')
    //         ->where('receiver', Auth::user()->id)
    //         ->orderBy('created_at', 'DESC')->get([
    //             'messenger.id',
    //             'first_name_en',
    //             'last_name_en',
    //             'first_name_ar',
    //             'last_name_ar',
    //             'subject',
    //             'read',
    //             'messenger.created_at',
    //             'messenger.sender'
    //         ]);

    //     $sentMessages = Messenger::where('messenger.sender', Auth::id())
    //         ->join('users', 'messenger.receiver', '=', 'users.id')
    //         ->orderBy('messenger.created_at', 'DESC')
    //         ->get([
    //             'messenger.id',
    //             'users.first_name_en',
    //             'users.last_name_en',
    //             'users.first_name_ar',
    //             'users.last_name_ar',
    //             'messenger.subject',
    //             'messenger.read',
    //             'messenger.receiver',
    //             'messenger.created_at'
    //         ]);


    //     return view('admin.clients.messages.index', compact('sentMessages', 'receivedMessages'));
    // }

    // public function index() {
    // $received = Messenger::leftJoin('users','sender','=','users.id')
    //             ->where('receiver',Auth::user()->id)
    //             ->orderBy('created_at','DESC')->get(['messenger.id','first_name_en','last_name_en','first_name_ar',
    //             'last_name_ar','subject','read','messenger.created_at','messenger.sender']);
    // return view('admin.clients.inbox')->with('received',$received);
    // }

    public function outbox()
    {
        $sent = Messenger::where('messenger.sender', Auth::id())
            ->join('users', 'messenger.receiver', '=', 'users.id')
            ->orderBy('messenger.created_at', 'DESC')
            ->get([
                'messenger.id',
                'users.first_name_en',
                'users.last_name_en',
                'users.first_name_ar',
                'users.last_name_ar',
                'messenger.subject',
                'messenger.read',
                'messenger.receiver',
                'messenger.created_at'
            ]);

        return view('admin.clients.outbox', compact('sent'));
    }
    public function openmsg(Request $request)
    {
        if ($request->type == 1) {
            $getmsg = Messenger::leftJoin('users', 'sender', '=', 'users.id')->where('receiver', Auth::user()->id)->where('messenger.id', $request->id)->first([
                'messenger.id',
                'first_name_en',
                'last_name_en',
                'first_name_ar',
                'last_name_ar',
                'subject',
                'message',
                'read',
                'messenger.sender',
                'messenger.created_at'
            ]);
            if (!$getmsg) {
                return response()->json('error');
            }
            $getmsg->read = 1;
            $getmsg->Save();
        } else {
            $getmsg = Messenger::leftJoin('users', 'receiver', '=', 'users.id')->where('sender', Auth::user()->id)->where('messenger.id', $request->id)->first([
                'messenger.id',
                'first_name_en',
                'last_name_en',
                'first_name_ar',
                'last_name_ar',
                'subject',
                'message',
                'read',
                'messenger.receiver',
                'messenger.created_at'
            ]);
            if (!$getmsg) {
                return response()->json('error');
            }
        }
        return response()->json($getmsg);
    }
    public function sendmsg(Request $request)
    {
        try {
            $new = new Messenger();
            $new->sender = $request->input('sender');
            $new->receiver = $request->input('receiver');
            $new->messgeId = $request->input('messgeId');
            $new->subject = $request->input('subject');
            $new->message = $request->input('message');
            $new->user_id = auth()->user()->id;
            $new->save();
            $rname = User::findOrFail($request->input('receiver'));
            $sname =  User::findOrFail($request->input('sender'));
            /// notify user vi push
            /// event(new WebMessage('NewMsg', $rname->first_name_en.' '.$rname->last_name_en, $request->input('receiver')));
            /// Send Mail
            $user = new User();
            $user->email = $rname->email;
            $user->id = $rname->id;
            $rfullname = $rname->first_name_en . " " . $rname->last_name_en;
            $sfullname = $sname->first_name_en . " " . $sname->last_name_en;
            $MSG = new TemplateEmail;
            $MSG->Message = ["New message received - VI portal", "Dear " . $rfullname . ",", "You have a new message from " . $sfullname . ', Needs your review.', "/dashboard/inbox"];
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }
            /// End of send

            return redirect()->back()->with('message', 'Done');
        } catch (Exception $e) {
            //log what happend//
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function MessengerTrash(Request $request){
        $trashedMessages = DB::table('messenger')
    ->whereNotNull('deleted_at')
    ->where(function($query) {
        $query->where('sender', Auth::id())
              ->orWhere('receiver', Auth::id());
    })
    ->get();
    return response()->json(['Trashed'=>$trashedMessages]);
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->input('id');
            $del = Messenger::where(function($query) {
                $query->where('sender', Auth::user()->id)
                      ->orWhere('receiver', Auth::user()->id);
            })
            ->where('id', $id)
            ->first();
            if ($del) {
                Messenger::Destroy($id);
                return response()->json([
                    'state' => 'success'
                ], 200);
            } else {
                return response()->json([
                    'state' => 'unauthorized'
                ], 401);
            }
        } catch (Exception $e) {
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            return response()->json([
                'state' => 'failed'
            ], 500);
        }
    }
    public function sendmessage(Request $request)
    {
        
        $users = $mentors = $cycles = $organizations = null;
        if (Auth::user()->hasAnyRole('Admin|OrganizationManager')) {
            $cycles = Cycle::select('id', 'title')->get();

            $users = Company::get();

            $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
            $organizations = Organization::all();
            if ($request->has('cycle_id') && !empty($request->cycle_id)) {
                $users = Company::where('cycle', $request->cycle_id)
                    ->whereHas('user', function ($query) {
                        $query->role('Registered');
                    })->get();
            }
            if ($request->has('organization_id') && !empty($request->organization_id)) {
                $users = Company::where('organization_id', $request->organization_id)->get();
            }
        } elseif (Auth::user()->hasRole('Mentor')) {
            $cycles = CyclesMentors::where('user_id', Auth::id())->get(['cycle_id'])->toArray();
            $users = Company::whereIn('cycle', array_values($cycles))->get();
            $mentors = null;
        } elseif (Auth::user()->company) {
            if (Auth::user()->company->approved) {
                $users = Company::where('cycle', Auth::user()->company->cycle)
                    ->where('id', '!=', Auth::user()->company->id)
                    ->whereHas('user', function ($query) {
                        $query->role('Registered');
                    })->get();

                $mentors = User::join('cycles_mentors', 'users.id', 'user_id')
                    ->where('cycle_id', Auth::user()->company->cycle)
                    ->get(['users.id', 'first_name_ar', 'first_name_en', 'last_name_ar', 'last_name_en']);
            }
        }

        return view('admin/clients/sendmessage', compact('users', 'mentors', 'cycles', 'organizations'));
    }

    public function getUsersByProgram(Request $request)
    {
        $cycleId = $request->input('cycle_id');

        $users = Company::where('cycle', $cycleId)
            ->whereHas('user', function ($query) {
                $query->role('Registered');
            })
            ->with('user')
            ->get();

        $formattedUsers = $users->map(function ($company) {
            return [
                'id' => $company->user->id,
                'name' => $company->user->first_name_en . ' ' . $company->user->last_name_en . ' - (' . $company->name_en . ')',
            ];
        });

        return response()->json(['users' => $formattedUsers]);
    }
    public function savemessage(Request $request)
    {
        
        $cuser = Auth::user();
        // if ($request->draftsNumber > 0) {
        //     DB::table('message_drafts')->where('drafts', $request->draftsNumber)->delete();
        // }
        if ($request->type == 1) {
            if (!$request->users) {
                return redirect('/dashboard')->with(['msg' => 'MessageError', 'class' => 'danger']);
            }
            foreach ($request->users as $key => $rec) {
                $rname = User::find($rec);
                if (!$rname) {
                    return redirect('/dashboard')->with(['msg' => 'Error: Selected User does not exist', 'class' => 'danger']);
                }

                try {
                    $new = new Messenger();
                    $new->sender = $cuser->id;
                    $new->receiver = $rec;
                    $new->subject = $request->input('subject');
                    $new->message = $request->input('message');
                    $new->save(); // Save to DB

                    // Prepare Email
                    $rfullname = $rname->first_name_en . " " . $rname->last_name_en;
                    $sfullname = $cuser->first_name_en . " " . $cuser->last_name_en;
                    $MSG = new TemplateEmail;
                    $MSG->Message = [
                        "New message received - VI portal",
                        "Dear " . $rfullname . ",",
                        "You have a new message from " . $sfullname . ", Needs your review.",
                        "/dashboard/inbox"
                    ];

                    // Send Notification (only in production)
                    if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                        try {
                            $rname->notify($MSG);
                        } catch (\Throwable $th) {
                            Log::error('Notification Error: ' . $th->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Database Error: ' . $e->getMessage());
                    return redirect('/dashboard')->with(['msg' => 'Database error: ' . $e->getMessage(), 'class' => 'danger']);
                }
            }
        } elseif ($request->type == 2) {
            if (!$request->mentors || !is_array($request->mentors)) {
                return redirect('/dashboard')->with(['msg' => 'MessageError: No mentors selected', 'class' => 'danger']);
            }

            foreach ($request->mentors as $rec) {
                $rname = User::find($rec);

                if (!$rname) {
                    return redirect('/dashboard')->with(['msg' => 'Error: Selected mentor does not exist', 'class' => 'danger']);
                }

                try {
                    $new = new Messenger();
                    $new->sender = $cuser->id;
                    $new->receiver = $rec;
                    $new->subject = $request->input('subject');
                    $new->message = $request->input('message');
                    $new->save();

                    // Prepare Email
                    $rfullname = $rname->first_name_en . " " . $rname->last_name_en;
                    $sfullname = $cuser->first_name_en . " " . $cuser->last_name_en;
                    $MSG = new TemplateEmail;
                    $MSG->Message = [
                        "New message received - VI portal",
                        "Dear " . $rfullname . ",",
                        "You have a new message from " . $sfullname . ", Needs your review.",
                        "/dashboard/inbox"
                    ];

                    // Send Notification (only in production)
                    if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                        try {
                            $rname->notify($MSG);
                        } catch (\Throwable $th) {
                            Log::error('Notification Error: ' . $th->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error: ' . $e->getMessage());
                    return redirect('/dashboard')->with(['msg' => 'error: ' . $e->getMessage(), 'class' => 'danger']);
                }
            }
        } elseif ($request->type == 3) {
            if (!$request->organizations || !is_array($request->organizations)) {
                return redirect('/dashboard')->with(['msg' => 'MessageError: No organizations selected', 'class' => 'danger']);
            }

            foreach ($request->organizations as $orgId) {
                $organization = Organization::find($orgId);
                if (!$organization) {
                    return redirect('/dashboard')->with(['msg' => 'Error: Selected organization does not exist', 'class' => 'danger']);
                }

                $orgUsers = User::where('organization_id', $orgId)->get();

                foreach ($orgUsers as $user) {
                    try {
                        $new = new Messenger();
                        $new->sender = $cuser->id;
                        $new->receiver = $user->id;
                        $new->subject = $request->input('subject');
                        $new->message = $request->input('message');
                        $new->save();

                        $rfullname = $user->first_name_en . " " . $user->last_name_en;
                        $sfullname = $cuser->first_name_en . " " . $cuser->last_name_en;
                        $MSG = new TemplateEmail;
                        $MSG->Message = [
                            "New message received - VI portal",
                            "Dear " . $rfullname . ",",
                            "You have a new message from " . $sfullname . ", Needs your review.",
                            "/dashboard/inbox"
                        ];

                        if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                            try {
                                $user->notify($MSG);
                            } catch (\Throwable $th) {
                                Log::error('Notification Error: ' . $th->getMessage());
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error(' Error: ' . $e->getMessage());
                        return redirect('/dashboard')->with(['msg' => 'error: ' . $e->getMessage(), 'class' => 'danger']);
                    }
                }
            }
        } else {
           
            try {
                $new = new Messages();
                $name = $cuser->first_name_en . ' ' . $cuser->last_name_en;
                $new->name = $name;
                $new->internal_id = $cuser->id;
                $new->phone = $cuser->phone;
                $new->email = $cuser->email;
                $new->subject = $request->input('subject');
                $new->message = $request->input('message');
                $new->userip = $request->getClientIp();
                $new->save();
                event(new WebMessage('mymsg', $name));
            } catch (\Exception $e) {
                Log::error('Error: ' . $e->getMessage());
                return redirect('/dashboard')->with(['msg' => 'error: ' . $e->getMessage(), 'class' => 'danger']);
            }
        }

        return redirect('/dashboard/messages')->with(['msg' => 'MessageSentSuccessfully', 'class' => 'success']);
    }
    public function draftmessage(Request $request)
    {
       
        $cuser = Auth::user();
        if ($request->draftsNumber > 0) {
            DB::table('message_drafts')->where('drafts', $request->draftsNumber)->delete();
        }
        
        $lastDraft = DB::table('message_drafts')->max('drafts');
        $drafts = $lastDraft ? $lastDraft + 1 : 1;
            
        if ($request->type == 1) {
            if (!$request->users) {
                return response()->json(['error' => __('admin.MessageError')]);
            }
            foreach ($request->users as $key => $rec) {
                $rname = User::find($rec);
                if (!$rname) {
                    return response()->json(['error' => __('admin.Selected User does not exist')]);
                }
                try {
                    $draft = new MessageDraft();
                    $draft->sender = $cuser->id;
                    $draft->receiver = $rec;
                    $draft->subject = $request->subject;
                    $draft->message = $request->message;
                    $draft->drafts = $drafts;
                    $draft->type = $request->type;
                    $draft->save(); // Save to DB
                    
                }catch (\Exception $e) {
                    Log::error('Database Error: ' . $e->getMessage());
                    return response()->json(['error' => 'Database error: ' . $e->getMessage() ]);
                }
            }
        } elseif ($request->type == 2) {
            if (!$request->mentors || !is_array($request->mentors)) {
                return response()->json(['error' => __('admin.No mentors selected')]);
            }

            foreach ($request->mentors as $rec) {
                $rname = User::find($rec);

                if (!$rname) {
                    return response()->json(['error' => __('admin.Selected mentor does not exist')]);
                }

                try {
                    $draft = new MessageDraft();
                    $draft->sender = $cuser->id;
                    $draft->receiver = $rec;
                    $draft->subject = $request->subject;
                    $draft->message = $request->message;
                    $draft->drafts = $drafts;
                    $draft->type = $request->type;
                    $draft->save(); // Save to DB
                    
                   
                } catch (\Exception $e) {
                    Log::error('Error: ' . $e->getMessage());
                    return response()->json(['error' => 'Database error: ' . $e->getMessage() ]);
                }
            }
        } elseif ($request->type == 3) {
            if (!$request->organizations || !is_array($request->organizations)) {
                return redirect('/dashboard/messages')->with(['msg' => 'MessageError: No organizations selected', 'class' => 'danger']);
            }
            foreach ($request->organizations as $orgId) {
                $organization = Organization::find($orgId);
                if (!$organization) {
                    return response()->json(['error' => __('admin.Selected organization does not exist')]);
                }
                $orgUsers = User::where('organization_id', $orgId)->get();
                foreach ($orgUsers as $user) {
                    try {
                        $draft = new MessageDraft();
                        $draft->sender = $cuser->id;
                        $draft->receiver = $user->id;
                        $draft->subject = $request->subject;
                        $draft->message = $request->message;
                        $draft->drafts = $drafts;
                        $draft->type = $request->type;
                        $draft->save(); // Save to DB

                    } catch (\Exception $e) {
                        Log::error(' Error: ' . $e->getMessage());
                        return response()->json(['error' => 'Database error: ' . $e->getMessage() ]);
                    }
                }
            }
        } 
        
        return response()->json([
            'success' => __('admin.MessageDraftSuccessfully'),
            'draftsNumber'=> $drafts ]);
    }
    public function deleteDraft(Request $request){
        $draft = DB::table('message_drafts')->where('drafts',$request->draftsNumber);
        if (!$draft) {
            return response()->json(['error' => __('admin.MessageError')]);
        }
        $draft->delete();
        return response()->json(['success' => __('admin.MessageDraftDeletedSuccessfully')]);
    }

    public function getAllDrafts() {
        $cuser = Auth::user();
        $drafts = DB::table('message_drafts')->where('sender', $cuser->id)->get();
        return response()->json(['drafts' => $drafts]);
    }

    public function toggleStarredStatus(Request $request)
    {
        try {
            $id = $request->id;
            $message = Messenger::where('id', $id)
            ->where(function($query) {
                $query->where('sender', Auth::id())
                      ->orWhere('receiver', Auth::id());
            })
            ->first();
            if ($message) {
                if($message->starred == 0){
                    $message->starred = 1;
                }else{
                    $message->starred = 0;
                }
                $message->save();
    
                return response()->json([
                    'state' => 'success',
                    'starred' => $message->starred
                ]);
            }
        } catch (Exception $e) {
            // تسجيل الخطأ
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            return response()->json([
                'state' => 'failed'
            ], 500);
        }
    }

    public function getAllStarred() {
       
        $starred = DB::table('messenger')
    ->where('starred', 1)
    ->where(function($query) {
        $query->where('sender', Auth::id())
              ->orWhere('receiver', Auth::id());
    })
    ->get();
    return response()->json(['Starred'=>$starred]);
    }

    public function toggleMarkReadStatus(Request $request)
    {
    try {
        // تحويل id من نص إلى مصفوفة
        $ids = json_decode($request->id);
        // التحقق من أن النتيجة مصفوفة
        if (!is_array($ids)) {
            // return response()->json(['state' => 'failed', 'message' => 'Invalid ID format'], 400);
            $ids = $request->id;
            $message = Messenger::where('id', $ids)
            ->where('receiver', Auth::id())
            ->first();
            if($request->mark == 'read'){
                $message->read = 1;
            }else{
                $message->read = 0;
            }
            $message->save();
        }else{
            $messages = Messenger::whereIn('id', $ids)
            ->where('receiver', Auth::id())
            ->get();
            foreach ($messages as $message) {
                if($request->mark == 'read'){
                    $message->read = 1;
                }else{
                    $message->read = 0;
                }
                $message->save();
            }
        }
        
        return response()->json([
            'state' => 'success',
            'updated_ids' => $ids
        ]);
    } catch (Exception $e) {
        // تسجيل الخطأ
        Log::channel('system_exceptions')->info('Exceptions:', [$e]);
        return response()->json([
            'state' => 'failed'
        ], 500);
    }
    }

    public function sendReplyMessage(Request $request)
    {
        $request->validate([
            'message_subject' => 'nullable|string|max:255',
            'message_message' => 'required|string',
            'message_receiver' => 'required|exists:users,id',
            'message_id' => 'nullable|exists:messenger,id',
        ]);
        
        $message = Messenger::create([
            'subject' => $request->message_subject,
            'message' => $request->message_message,
            'receiver' => $request->message_receiver,
            'sender' => auth()->id(),
            'reply_id' => $request->message_id,
        ]);

    // جلب الرسالة مع الرسالة الأصلية اللي بيرد عليها
    $replyWithSender = Messenger::with('reply')->find($message->id);

    
        return response()->json(['message' => 'Message sent successfully', 'messages' => $replyWithSender]);
    }


    private function messenger_query( $id = null, $request )
    {
        $l = app()->getLocale();
        $userId = Auth::id();
        $query = "SELECT
                m.id,
                m.sender AS sender_id,
                sender.first_name_en AS sender_first_name_en,
                sender.last_name_en AS sender_last_name_en,
                sender.first_name_ar AS sender_first_name_ar,
                sender.last_name_ar AS sender_last_name_ar,
                m.receiver AS receiver_id,
                m.subject,
                m.message,
                m.read,
                m.created_at,
                reply.id AS reply_id,
                reply.subject AS reply_subject,
                reply.message AS reply_message,
                reply.created_at AS reply_created_at,
                reply.sender AS reply_sender_id,
                reply_sender.first_name_en AS reply_sender_first_name_en,
                reply_sender.last_name_en AS reply_sender_last_name_en,
                reply_sender.first_name_ar AS reply_sender_first_name_ar,
                reply_sender.last_name_ar AS reply_sender_last_name_ar
            FROM
                messenger m
            LEFT JOIN
                users sender ON m.sender = sender.id
            LEFT JOIN
                messenger reply ON reply.reply_id = m.id
            LEFT JOIN
                users reply_sender ON reply.sender = reply_sender.id
            WHERE
                 m.reply_id IS NULL
        ";
        if( !is_null( $id ) )
        {
            $query .= " AND m.id = $id";
        }
        else
        {
            $type = $request->type ?? 'inbox';
           switch ($type) {
        case 'inbox':
            $query .= " AND m.receiver = $userId AND m.deleted_at IS NULL";
            break;
        case 'sent':
            $query .= " AND m.sender = $userId AND m.deleted_at IS NULL";
            break;
        case 'starred':
            $query .= " AND m.starred = 1 AND (m.sender = $userId OR m.receiver = $userId) AND m.deleted_at IS NULL";
            break;
        case 'trashed':
            $query .= " AND m.deleted_at IS NOT NULL AND (m.sender = $userId OR m.receiver = $userId)";
            break;
    }
        }

        $messages = collect( DB::select( $query ) );
        $messages = $messages->groupBy('id')->map(function($groupedMessage) use ($l) {
            $msg = $groupedMessage->first();
            $message = new stdClass;
            $message->id = $msg->id;
            $message->subject = $msg->subject;
            $message->body = $msg->message;
            $message->read = $msg->read;
            $message->created_at = $msg->created_at;
            $message->sender = new stdClass;
            $message->sender->id = $msg->sender_id;
            $message->sender->name = $msg->{'sender_first_name_' . $l} . ' ' . $msg->{'sender_last_name_' . $l};
            $message->replies = $groupedMessage->filter(function($reply) { return !is_null( $reply->reply_id ); })->map(function($_reply) use ($l) {
                $reply = new stdClass;
                $reply->id = $_reply->reply_id;
                $reply->subject = $_reply->reply_subject;
                $reply->body = $_reply->reply_message;
                $reply->created_at = $_reply->reply_created_at;
                $reply->sender = new stdClass;
                $reply->sender->id = $_reply->reply_sender_id;
                $reply->sender->name = $_reply->{'reply_sender_first_name_' . $l} . ' ' . $_reply->{'reply_sender_last_name_' . $l};
                return $reply;
            })->values();
            return $message;
        })->values();
        if( !is_null( $id ) ) { $messages = $messages->first(); }
        return $messages;
    }

}
