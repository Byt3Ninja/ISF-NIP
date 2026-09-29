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
        $user = auth()->user();
        $userId = $user->id;
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
        if( $user->hasAnyRole('Admin|OrganizationManager|subadmin'))
        {
            if ($user->hasRole('Admin')) {
                $cycles = Cycle::select('id', 'title')->get();
                $users = Company::get();
                $organizations = Organization::all();
            } else {
                $orgIds = $user->organizations_ids;
                $cycles = Cycle::whereIn('organization_id', $orgIds)->select('id', 'title')->get();
                $users = Company::with(['user'])->whereHas('user', function ($query) use ($orgIds) {
                    $query->whereIn('organization_id', $orgIds);
                })
                ->get();
                $organizations = Organization::whereIn('id', $orgIds)->get();
            }
            // $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
			$mentors = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
				->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
				->where('roles.default_for', 'mentor')
				->select('users.*')
				->get();
        }
        elseif( $user->hasRole('Mentor'))
        {
            $cycles = CyclesMentors::where('user_id', $userId)->get(['cycle_id'])->toArray();
            $users = Company::whereIn('cycle', array_values($cycles))->get();
        }
        elseif( $user->company )
        {
            if( $user->company->approved )
            {
               $users = Company::where('cycle', $user->company->cycle)
                ->where('id', '!=', $user->company->id)
                ->whereHas('user.roles', function ($query) {
                     $query->where('name', 'registered');
                })
             ->get();
                $mentors = User::join('cycles_mentors', 'users.id', 'user_id')
                    ->where('cycle_id', $user->company->cycle)
                    ->get(['users.id', 'first_name_ar', 'first_name_en', 'last_name_ar', 'last_name_en']);
            }
        }
       
        // $drafts = DB::table('message_drafts')->where('sender', $userId)->get();

        // $drafts = MessageDraft::leftJoin('users', 'sender', '=', 'users.id')
        //     ->where('sender', $userId)
        //     ->orderBy('created_at', 'DESC')
        //     ->get([
        //         'message_drafts.id',
        //         'first_name_en',
        //         'last_name_en',
        //         'first_name_ar',
        //         'last_name_ar',
        //         'subject',
        //         'message',
        //         'message_drafts.created_at',
        //         'message_drafts.sender'
        //     ]);

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

            $rawDrafts = MessageDraft::leftJoin('users as sender_user', 'sender', '=', 'sender_user.id')
                ->leftJoin('users as receiver_user', 'receiver', '=', 'receiver_user.id')
                ->leftJoin('companies as co', 'co.id', '=', 'receiver_user.company_id')
                ->leftJoin('organizations as org', 'org.id', '=', 'receiver_user.organization_id')
                ->leftJoin('cycles as cy', 'cy.id', '=', 'message_drafts.cycle_id')
                ->where('sender', $userId)
                ->orderBy('message_drafts.created_at', 'DESC')
                ->get([
                    'message_drafts.id',
                    'message_drafts.drafts',
                    'message_drafts.subject',
                    'message_drafts.message',
                    'message_drafts.created_at',
                    'message_drafts.sender',
                    'message_drafts.receiver',
                    'message_drafts.type',

                    // بيانات المرسل
                    'sender_user.first_name_en as sender_first_name_en',
                    'sender_user.last_name_en as sender_last_name_en',
                    'sender_user.first_name_ar as sender_first_name_ar',
                    'sender_user.last_name_ar as sender_last_name_ar',
                    'sender_user.img_url as img_url',

                    // بيانات المستقبل
                    'receiver_user.id as receiver_id',
                    'receiver_user.first_name_en as receiver_first_name_en',
                    'receiver_user.last_name_en as receiver_last_name_en',
                    'receiver_user.first_name_ar as receiver_first_name_ar',
                    'receiver_user.last_name_ar as receiver_last_name_ar',

                    'co.id as company_id',
                    'co.name_en as company_name_en',
                    'co.name_ar as company_name_ar',
                    'co.logo as company_logo_url',

                    'org.id as organization_id',
                    'org.name_en as organization_name_en',
                    'org.name_ar as organization_name_ar',
                    'org.img_url as organization_img_url',

                    'cy.id as cycle_id',
                    'cy.title as cycle_title',
                ]);

            // groupBy drafts
            $drafts = $rawDrafts->groupBy('drafts');
        
            $folders = collect([
                ['target' => 'inbox', 'icon' => 'ti-inbox', 'label' => 'Inbox', 'badge' => ['count' => $this->getMessageCountByType($userId, 'inbox'), 'class' => 'bg-label-primary']],
                ['target' => 'sent', 'icon' => 'ti-send', 'label' => 'Sent', 'badge' => ['count' => $this->getMessageCountByType($userId, 'sent'), 'class' => 'bg-label-success']],
                ['target' => 'draft', 'icon' => 'ti-edit', 'label' => 'Draft', 'badge' => ['count' => count($drafts), 'class' => 'bg-label-secondary']],
                ['target' => 'starred', 'icon' => 'ti-star', 'label' => 'Starred', 'badge' => ['count' => $this->getMessageCountByType($userId, 'starred'), 'class' => 'bg-label-warning']],
                ['target' => 'trash', 'icon' => 'ti-trash', 'label' => 'Trash', 'badge' => ['count' => $this->getMessageCountByType($userId, 'trash'), 'class' => 'bg-label-danger']],
            ]);


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
        'folders'
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
            $user = auth()->user();
            // $senderId = $request->has('sender') ? $request->input('sender', null) : null;
            // $senderId = $senderId ?? $user->id;
            $senderId = $user->id;

            $receiverId = $request->has('receiver') ? $request->input('receiver', null) : null;
            if( ( !$receiverId || is_null( $receiverId ) ) && $request->has('company_id') )
            {
                $company = Company::find( $request->input('company_id') );
                if( $company?->user?->id )
                {
                    $receiverId = $company?->user?->id ?? null;
                }
            }
            if( !$receiverId || is_null( $receiverId ) )
            {
                return redirect()->back()->with('error', 'Receiver not specified');
            }

            $new = new Messenger();
            $new->sender = $senderId;
            $new->receiver = $receiverId;
            $new->messgeId = $request->input('messgeId');
            $new->subject = $request->input('subject');
            $new->message = $request->input('message');
            // $new->user_id = auth()->user()->id;
            $new->save();
            $rname = User::findOrFail( $receiverId );
            $sname =  User::findOrFail( $senderId );
            /// notify user vi push
            /// event(new WebMessage('NewMsg', $rname->first_name_en.' '.$rname->last_name_en, $receiverId));
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
                // حذف جميع الردود التابعة لها
                Messenger::where('reply_id', $id)->delete();
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
        $authUser = Auth::user();
        if ($authUser->hasAnyRole('Admin|OrganizationManager|subadmin')) {
            if ($authUser->hasRole('Admin')) {
                $cycles = Cycle::select('id', 'title')->get();
                $users = Company::get();
                $organizations = Organization::all();
            } else {
                $orgIds = $authUser->organizations_ids;
                $cycles = Cycle::whereIn('organization_id', $orgIds)->select('id', 'title')->get();
                $users = Company::whereIn('organization_id', $orgIds)->get();
                $organizations = Organization::whereIn('id', $orgIds)->get();
            }

            // $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
			$mentors = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
				->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
				->where('roles.default_for', 'mentor')
				->select('users.*')
				->get();
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
        $cycleId = $request->input('id');
       
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
                if(Auth::user()->hasRole('Registered')){
                    $cycle = Auth::user()->company->cycle;

                // جلب organization_id من الجدول
                $org_id = DB::table('cycles')->where('id', $cycle)->value('organization_id');

                // استخدامه للبحث عن المستخدمين
                $org = Organization::where('id', $org_id)->first();
                $orgUsers = User::where('id', $org->user_id)->get();
                foreach ($orgUsers as $user) {
                 
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
                   
                }

                // $new = new Messages();
                // $name = $cuser->first_name_en . ' ' . $cuser->last_name_en;
                // $new->name = $name;
                // $new->internal_id = $cuser->id;
                // $new->phone = $cuser->phone;
                // $new->email = $cuser->email;
                // $new->subject = $request->input('subject');
                // $new->message = $request->input('message');
                // $new->userip = $request->getClientIp();
                // $new->save();

            }elseif (Auth::user()->hasRole('organizationmanager')) {

                
                $orgUsers = User::role('Admin')->get();
                foreach ($orgUsers as $user) {
                 
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
                   
                }






            }


                } catch (\Exception $e) {
                    Log::error(' Error: ' . $e->getMessage());
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
                    $draft->cycle_id = $request->cycleId;
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

            $organizations = Organization::whereIn('id', $request->organizations)->pluck('id');
            $missingOrgId = collect($request->organizations)->first(fn ($orgId) => !$organizations->contains($orgId));
            if ($missingOrgId !== null) {
                return response()->json(['error' => __('admin.Selected organization does not exist')]);
            }

            $orgUsers = User::whereIn('organization_id', $request->organizations)->get();

            try {
                $now = now();
                $rows = $orgUsers->map(fn ($user) => [
                    'sender' => $cuser->id,
                    'receiver' => $user->id,
                    'subject' => $request->subject,
                    'message' => $request->message,
                    'drafts' => $drafts,
                    'type' => $request->type,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if (!empty($rows)) {
                    MessageDraft::insert($rows);
                }
            } catch (\Exception $e) {
                Log::error(' Error: ' . $e->getMessage());
                return response()->json(['error' => 'Database error: ' . $e->getMessage() ]);
            }
        }
        
        return response()->json([
            'success' => __('admin.MessageDraftSuccessfully'),
            'draftsNumber'=> $drafts ]);
    }
    public function deleteDraft(Request $request){

        
        $draft = DB::table('message_drafts')->where('id',$request->draftsNumber)->first();
        $drafts = $draft->drafts;
        
        if (!$draft) {
            return response()->json(['error' => __('admin.MessageError')]);
        }
        $delete = DB::table('message_drafts')->where('drafts', $drafts)->delete();
        
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
        $l = app()->getLocale();

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
            'sender_name' => auth()->user()->first_name_.$l. ' ' . auth()->user()->last_name_.$l,
            'reply_id' => $request->message_id,
        ]);
        $reply_id = $message->id;

    // جلب الرسالة مع الرسالة الأصلية اللي بيرد عليها
    $replyWithSender1 = Messenger::with('reply')->find($message->id);
    $replyWithSender2 = DB::table('messenger as m')
    ->leftJoin('users as u', 'm.sender', '=', 'u.id')
    ->leftJoin('users as ur', 'm.receiver', '=', 'ur.id')
    ->leftJoin('messenger as reply', 'reply.sender', '=', 'ur.id')
    ->leftJoin('users as urs', 'reply.sender', '=', 'urs.id')
    ->where('m.id', $request->message_id)
    ->get([
        'm.id',
        'm.subject',
        'm.message as message',
        'm.created_at',
        'm.updated_at',
        'm.deleted_at',
        'm.sender as sender',
        'm.receiver as receiver',
        'm.reply_id',
        'm.read',
        'm.reported',
        'm.starred',
        'm.messgeId',

        // reply fields
        'reply.id as reply_id',
        'reply.subject as reply_subject',
        'reply.message as reply_message',
        'reply.created_at as reply_created_at',
        'reply.updated_at as reply_updated_at',
        'reply.deleted_at as reply_deleted_at',
        'reply.sender as reply_sender',
        'reply.receiver as reply_receiver',
        'reply.read as reply_read',
        'reply.updated_at as reply_updated_at',
        'reply.reported as reply_reported',
        'reply.starred as reply_starred',
        'reply.messgeId as reply_messgeId',
        'reply.reply_id as reply_reply_id',
        'urs.first_name_en as reply_first_name_en',
        'urs.last_name_en as reply_last_name_en',
        'urs.first_name_ar as reply_first_name_ar',
        'urs.last_name_ar as reply_last_name_ar',
        'urs.img_url as reply_img_url',]);

        $messages = $replyWithSender2->groupBy('id')->map(function ($groupedMessage) use ($l, $reply_id) {
            $msg = $groupedMessage->first();
            $message = new stdClass;
            $message->id = $msg->id;
            $message->reply_id = $msg->reply_id;
            $message->sender = $msg->sender;
            $message->receiver = $msg->receiver;
            $message->subject = $msg->subject;
            $message->message = $msg->message;
            $message->read = $msg->read;
            $message->starred = $msg->starred;
            $message->created_at = $msg->created_at;
            $message->reply = $groupedMessage->filter(fn($reply) => !is_null($reply->reply_id))->map(function ($_reply) use ($l, $reply_id) {
                $reply = new stdClass;
                $reply->id = $_reply->reply_id;
                $reply->subject = $_reply->reply_subject;
                $reply->message = $_reply->reply_message;
                $reply->receiver = $_reply->reply_receiver;
                $reply->reply_updated_at = $_reply->reply_updated_at;
                $reply->created_at = $_reply->reply_created_at;
                $reply->sender = $_reply->reply_sender;
                $reply->sender_name = $_reply->{"reply_first_name_".$l} . ' ' . $_reply->{"reply_last_name_".$l};
                return $reply;
            })->where('id', $reply_id)->values()->first();
            return $message;
        })->values();
        
        
        $message = collect($messages)->first();
    
        return response()->json(['message' => 'Message sent successfully', 'messages' => $message]);
    }


    private function messenger_query( $id = null, $request )
    {
        $l = app()->getLocale();
        $userId = (int) Auth::id();
        $type = $request->type ?? 'inbox';

        if ($type === 'draft') {
            // استعلام الرسائل المحفوظة كمسودات
            $rawDrafts = MessageDraft::leftJoin('users as sender_user', 'sender', '=', 'sender_user.id')
                ->leftJoin('users as receiver_user', 'receiver', '=', 'receiver_user.id')
                ->leftJoin('companies as co', 'co.id', '=', 'receiver_user.company_id')
                ->leftJoin('organizations as org', 'org.id', '=', 'receiver_user.organization_id')
                ->leftJoin('cycles as cy', 'cy.id', '=', 'message_drafts.cycle_id')
                ->where('sender', $userId)
                ->orderBy('message_drafts.created_at', 'DESC')
                ->get([
                    'message_drafts.id',
                    'message_drafts.drafts',
                    'message_drafts.subject',
                    'message_drafts.message',
                    'message_drafts.created_at',
                    'message_drafts.sender',
                    'message_drafts.receiver',
                    'message_drafts.type',

                    // بيانات المرسل
                    'sender_user.first_name_en as sender_first_name_en',
                    'sender_user.last_name_en as sender_last_name_en',
                    'sender_user.first_name_ar as sender_first_name_ar',
                    'sender_user.last_name_ar as sender_last_name_ar',
                    'sender_user.img_url as img_url',

                    // بيانات المستقبل
                    'receiver_user.id as receiver_id',
                    'receiver_user.first_name_en as receiver_first_name_en',
                    'receiver_user.last_name_en as receiver_last_name_en',
                    'receiver_user.first_name_ar as receiver_first_name_ar',
                    'receiver_user.last_name_ar as receiver_last_name_ar',

                    'co.id as company_id',
                    'co.name_en as company_name_en',
                    'co.name_ar as company_name_ar',
                    'co.logo as company_logo_url',

                    'org.id as organization_id',
                    'org.name_en as organization_name_en',
                    'org.name_ar as organization_name_ar',
                    'org.img_url as organization_img_url',

                    'cy.id as cycle_id',
                    'cy.title as cycle_title',
                ]);

            // groupBy drafts
            $grouped = $rawDrafts->groupBy('drafts');

            // إنشاء المخرجات
            $messages = $grouped->map(function ($group) use ($l) {
                $first = $group->first();

                $message = new stdClass;
                $message->id = $first->id;
                $message->subject = $first->subject;
                $message->body = $first->message;
                $message->type = $first->type;
                $message->created_at = $first->created_at;

                // المرسل
                $message->sender = new stdClass;
                $message->sender->id = $first->sender;
                $message->sender->img_url = $first->img_url;
                $message->sender->name = $first->{'sender_first_name_' . $l} . ' ' . $first->{'sender_last_name_' . $l};

                // المستقبلين داخل المجموعة
                $message->receivers = $group->map(function ($msg) use ($l) {
                    return (object)[
                        'id' => $msg->receiver_id,
                        'name' => $msg->{'receiver_first_name_' . $l} . ' ' . $msg->{'receiver_last_name_' . $l},
                        'company_id' => $msg->company_id,
                        'company_name' => $msg->{'company_name_' . $l},
                        'company_logo_url' => $msg->company_logo_url,
                        'organization_id' => $msg->organization_id,
                        'organization_name' => $msg->{'organization_name_' . $l},
                        'organization_img_url' => $msg->organization_img_url,
                        'cycle_id' => $msg->cycle_id,
                        'cycle_title' => $msg->cycle_title,
                    ];
                })->unique('id')->values(); // إزالة التكرار

                return $message;
            })->values();

            return $messages;

        }

        // باقي الحالات: inbox, sent, starred, trash
        $query = "
            SELECT
                m.id,
                m.sender AS sender_id,
                sender.first_name_$l AS sender_first_name,
                sender.last_name_$l AS sender_last_name,
                sender.img_url AS img_url,
                m.receiver AS receiver_id,
                m.subject,
                m.message,
                m.read,
                m.starred,
                m.created_at,
                reply.id AS reply_id,
                reply.subject AS reply_subject,
                reply.message AS reply_message,
                reply.created_at AS reply_created_at,
                reply.sender AS reply_sender_id,
                reply_sender.first_name_$l AS reply_sender_first_name,
                reply_sender.last_name_$l AS reply_sender_last_name
            FROM
                messenger m
            LEFT JOIN
                users sender ON m.sender = sender.id
            LEFT JOIN
                messenger reply ON reply.reply_id = m.id
            LEFT JOIN
                users reply_sender ON reply.sender = reply_sender.id
            WHERE
                
        ";

        switch ($type) {
            case 'inbox':
                $query .= "( (m.receiver = $userId AND m.reply_id IS NULL) OR (reply.receiver = $userId) ) AND m.deleted_at IS NULL";
                // $query .= " m.receiver = $userId AND m.deleted_at IS NULL AND m.reply_id IS NULL";
                break;
            case 'sent':
                $query .= "  m.sender = $userId AND m.deleted_at IS NULL";
                break;
            case 'starred':
                $query .= "  m.starred = 1 AND (m.sender = $userId OR m.receiver = $userId) AND m.deleted_at IS NULL AND m.reply_id IS NULL";
                break;
            case 'trash':
                $query .= "  m.deleted_at IS NOT NULL AND (m.sender = $userId OR m.receiver = $userId) AND m.reply_id IS NULL";
                break;
        }

        $messages = collect(DB::select($query));

        $messages = $messages->groupBy('id')->map(function ($groupedMessage) use ($l) {
            $msg = $groupedMessage->first();
            $message = new stdClass;
            $message->id = $msg->id;
            $message->subject = $msg->subject;
            $message->body = $msg->message;
            $message->read = $msg->read;
            $message->starred = $msg->starred;
            $message->created_at = $msg->created_at;
            $message->sender = new stdClass;
            $message->sender->id = $msg->sender_id;
            $message->sender->img_url = $msg->img_url;
            $message->sender->name = $msg->{"sender_first_name"} . ' ' . $msg->{"sender_last_name"};
            $message->replies = $groupedMessage->filter(fn($reply) => !is_null($reply->reply_id))->map(function ($_reply) use ($l) {
                $reply = new stdClass;
                $reply->id = $_reply->reply_id;
                $reply->subject = $_reply->reply_subject;
                $reply->body = $_reply->reply_message;
                $reply->created_at = $_reply->reply_created_at;
                $reply->sender = new stdClass;
                $reply->sender->id = $_reply->reply_sender_id;
                $reply->sender->name = $_reply->{"reply_sender_first_name"} . ' ' . $_reply->{"reply_sender_last_name"};
                return $reply;
            })->values();
            return $message;
        })->values();
        
        return $messages;
        
    }
    private function getMessageCountByType($userId, $type)
    {
        $where = '';

        switch ($type) {
            case 'inbox':
                $where = "receiver = $userId AND deleted_at IS NULL AND reply_id IS NULL";
                break;
            case 'sent':
                $where = "sender = $userId AND deleted_at IS NULL";
                break;
            case 'starred':
                $where = "starred = 1 AND (sender = $userId OR receiver = $userId) AND deleted_at IS NULL AND reply_id IS NULL";
                break;
            case 'trash':
                $where = "deleted_at IS NOT NULL AND (sender = $userId OR receiver = $userId) AND reply_id IS NULL";
                break;
            default:
                return 0;
        }

        return DB::table('messenger')->whereRaw($where)->count();
    }
}
