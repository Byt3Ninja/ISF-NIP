<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Messenger;
use App\Services\MessagingRecipients;
use Carbon\Carbon;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleStarRequest;
use App\Http\Requests\MoveToTrashRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ReplyMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\ToggleStatusRequest;

class MessageController extends Controller
{
    use ApiResponder;
    /**
 * @OA\Post(
 *     path="/api/messages",
 *     summary="Send a new message or save as draft",
 *     description="Creates a new message to recipients or saves it as a draft based on the draft flag.",
 *     operationId="storeMessage",
 *     tags={"Messages"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"userTypeValue","userId","title","subject","draft"},
 *             @OA\Property(property="userTypeValue", type="integer", example=0, description="0: same org, 1: company, 2: specific users, 3: by org id"),
 *             @OA\Property(property="userId", type="array", @OA\Items(type="integer"), example={103,104}),
 *             @OA\Property(property="title", type="string", example="Team Standup Notes"),
 *             @OA\Property(property="subject", type="string", example="Summary of today's meeting"),
 *             @OA\Property(property="draft", type="boolean", example=false)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Message sent or draft saved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Message Sent Successfully"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No recipients found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No Recipients Found")
 *         )
 *     )
 * )
 */
    public function store(StoreMessageRequest $request)
    {
        $senderId = auth()->id();
        $sender   = auth()->user();
        if (!$sender->ableToAny(['messages.send', 'messages.save'])) {
          return $this->forbidden(__('api.forbidden'));
        }

        $isDraft  = $request->draft;
        $userIds = is_array($request->userId) ? $request->userId : [$request->userId];
       if ($isDraft) {
          $savedDrafts = [];

        foreach ($userIds as $receiverId) {
            $draftId = DB::table('message_drafts')->insertGetId([
                'sender'     => $senderId,
                'receiver'   => $receiverId,
                'cycle_id'   => $request->cycleId,
                'subject'    => $request->subject,
                'message'    => $request->message,
                'drafts'     => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $savedDrafts[] = [
                'id'        => $draftId,
                'type'      => 'draft',
                'subject'   => $request->subject,
                'message'   => $request->message,
                'createdBy' => $sender->name,
                'createdAt' => now()->toISOString(),
                'status'    => 'unread',
                'starred'   => false
            ];
        }
        return $this->created($savedDrafts, __('api.Draft_saved_successfully'));
       }

      $recipients = collect();
      // Same recipient rule as the web Messenger (MessengerController::savemessage).
      $allowed = app(MessagingRecipients::class);

      if ($request->userTypeValue == 1) {
        $recipients = User::whereHas('company')
            ->when($request->userId, function ($q) use ($request) {
                $q->whereIn('id', $request->userId);
            })
            ->get();

      } elseif ($request->userTypeValue == 2) {
        $recipients = User::whereIn('id', $request->userId ?? [])->get();

      } elseif ($request->userTypeValue == 3) {
        // "Whole organization" sends: a non-admin may only address organizations in their scope.
        $targetOrgIds = array_values(array_intersect(array_map('intval', $request->userId ?? []), $allowed->organizationIds($sender)));
        $recipients = User::whereIn('organization_id', $targetOrgIds)->get();

      } else {
        // A non-admin with no organizations reaches nobody (this used to fall through to every user).
        $user = auth()->user();
        $orgIds = $allowed->organizationIds($user);
        $recipients = User::when(!$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereIn('organization_id', $orgIds);
        })->get();
      }

      // Named recipients (types 1 and 2) must each be someone the sender may message;
      // an open type-1 audience ("all startups") is narrowed to those people.
      if (!$sender->hasRole('admin') && in_array((int) $request->userTypeValue, [1, 2], true)) {
        if (!empty($request->userId)) {
          foreach ($recipients as $receiver) {
            if (!$allowed->canMessage($sender, $receiver)) {
              return $this->forbidden(__('api.forbidden'));
            }
          }
        } else {
          $recipients = $recipients->filter(fn ($receiver) => $allowed->canMessage($sender, $receiver))->values();
        }
      }

      if ($recipients->isEmpty()) {
        return $this->error(__('api.No_recipients_found'), 404);
      }

       $insertedMessages = [];
        foreach ($recipients as $receiver) {
        $messageId = DB::table('messenger')->insertGetId([
            'sender'    => $senderId,
            'receiver'  => $receiver->id,
            'subject'   => $request->subject,
            'message'   => $request->message,
            'read'      => 0,
            'starred'   => 0,
            'created_at'=> now(),
            'updated_at'=> now()
        ]);

        $insertedMessages[] = [
            'id'        => $messageId,
            'type'      => 'outbox',
            'subject'   => $request->subject,
            'message'     => $request->message,
            'createdBy' => $sender->name,
            'createdAt' => now()->toISOString(),
            'status'    => 'unread',
            'starred'   => false
        ];
       }

     return $this->created(
        count($insertedMessages) === 1 ? $insertedMessages[0] : $insertedMessages,
        __('api.Message_sent_successfully')
     );

    }

    /**
 * @OA\Post(
 *     path="/api/messages/reply",
 *     summary="Reply to a message",
 *     description="Creates a reply to an existing message.",
 *     operationId="replyMessage",
 *     tags={"Messages"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"messageId","subject","message"},
 *             @OA\Property(property="messageId", type="integer", example=45),
 *             @OA\Property(property="subject", type="string", example="Re: Project Update"),
 *             @OA\Property(property="message", type="string", example="Thanks for the update, looks good!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Reply sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Reply Sent Successfully"),
 *             @OA\Property(property="data", type="object", example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Message not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Message Not Found")
 *         )
 *     )
 * )
 */
      public function reply(ReplyMessageRequest $request)
    {
        $user = auth()->user();
        if (!$user->ableTo('messages.reply')) {
          return $this->forbidden(__('api.forbidden'));
        }
        $originalMessage = DB::table('messenger')
        ->where('id', $request->messageId)
        ->first();

      if (!$originalMessage) {
        return $this->error(__('api.Message_not_found'), 404);
      }

      // Only a participant of the original thread may reply into it; otherwise
      // any user could inject a message into an arbitrary conversation.
      if ((int) $originalMessage->sender !== (int) $user->id
          && (int) $originalMessage->receiver !== (int) $user->id) {
        return $this->error(__('api.Message_not_found'), 404);
      }

      DB::table('messenger')->insert([
        'sender'   => auth()->id(),
        'receiver' => $originalMessage->sender,
        'reply_id'    => $request->messageId,
        'subject'     => $request->subject,
        'message'     => $request->message,
        'created_at'  => now(),
        'updated_at'  => now(),
     ]);

        return $this->created(null, __('api.Reply_sent_successfully'));
    }

    /**
 * @OA\Patch(
 *     path="/api/messages/toggle-status",
 *     summary="Toggle read/unread status of messages",
 *     description="Updates the read status for one or more messages by IDs.",
 *     operationId="toggleMessageStatus",
 *     tags={"Messages"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"messageIds","status"},
 *             @OA\Property(
 *                 property="messageIds",
 *                 type="array",
 *                 @OA\Items(type="integer"),
 *                 example={45,46}
 *             ),
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 enum={"read","unread"},
 *                 example="read"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message status updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Message Status Updated Successfully"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     )
 * )
 */
    public function toggleStatus(ToggleStatusRequest $request)
    {
      $user = auth()->user();
      if (!$user->ableToAny(['messages.read_status_mark_read', 'messages.read_status_mark_unread'])) {
          return $this->forbidden(__('api.forbidden'));
      }

      $ids = $request->messageIds;

      if (!is_array($ids)) {
        $ids = explode(',', $ids);
      }

     $ids = array_map('trim', $ids);

     $statusValue = $request->status === 'read' ? 1 : 0;
     // Scope to the caller's own conversations: without this, client-supplied
     // ids flip the read flag on other users' messages (same guard toggleStar uses).
     Messenger::whereIn('id', $ids)
        ->where(function ($q) use ($user) {
            $q->where('receiver', $user->id)->orWhere('sender', $user->id);
        })
        ->update(['read' => $statusValue]);

      return $this->success(null, [], __('api.message_status_updated'));
    }

    /**
 * @OA\Patch(
 *     path="/api/messages/star",
 *     summary="Toggle star status of messages",
 *     description="Updates the starred status for one or more messages by IDs.",
 *     operationId="toggleStar",
 *     tags={"Messages"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"messageId","starred"},
 *             @OA\Property(
 *                 property="messageId",
 *                 type="array",
 *                 @OA\Items(type="integer", example=45),
 *                 example={45,46}
 *             ),
 *             @OA\Property(
 *                 property="starred",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message star status updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Message Star Status Updated Successfully"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Message not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Message Not Found"),
 *             @OA\Property(property="data", type="object", example=null)
 *         )
 *     )
 * )
 */
    public function toggleStar(ToggleStarRequest $request)
    {
        $user = auth()->user();
        if (!$user->ableTo('messages.star_toggle')) {
          return $this->forbidden(__('api.forbidden'));
        }
        $ids = is_array($request->messageIds) ? $request->messageIds : explode(',', $request->messageIds);

        Messenger::whereIn('id', $ids)->where('receiver', $user->id)->update([
              'starred' => $request->starred === true ? 1 : 0
         ]);
        return $this->success(null, [], __('api.Message_star_status_updated'));
    }

    /**
     * @OA\Get(
     *     path="/api/messages",
     *     summary="List messages with filtering, search, and pagination",
     *     tags={"Messages"},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=20)),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", enum={"inbox", "outbox", "draft", "trash", "starred"})),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"read", "unread"})),
     *     @OA\Parameter(name="starred", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", enum={"createdAt", "title", "status"})),
     *     @OA\Parameter(name="order", in="query", required=false, @OA\Schema(type="string", enum={"asc", "desc"})),
     *     @OA\Response(
     *         response=200,
     *         description="Messages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Messages retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('messages.list')) {
          return $this->forbidden(__('api.forbidden'));
    }
    $validator = Validator::make($request->all(), [
        'page'   => 'nullable|integer|min:1',
        'limit'  => 'nullable|integer|min:1|max:100',
        'search' => 'nullable|string',
        'type'   => 'nullable|in:inbox,outbox,draft,starred,trash',
        'status' => 'nullable|in:read,unread',
        'starred'=> 'nullable|boolean',
        'sort'   => 'nullable|in:createdAt',
        'order'  => 'nullable|in:asc,desc',
    ]);

    if ($validator->fails()) {
        return  $this->validationError(
                __('api.validation_error'),
                422,
                $validator->errors()
        );
    }
    $type = $request->get('type');
    $limit = min($request->get('limit', 20), 100);
    $page = $request->get('page', 1);

    if ($type === 'draft') {
        $draftsQuery = DB::table('message_drafts')
            ->where('sender', $user->id);

        if ($request->filled('search')) {
            $draftsQuery->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                  ->orWhere('message', 'like', '%' . $request->search . '%');
            });
        }

        $sortField = $request->get('sort', 'createdAt');
        $sortOrder = $request->get('order', 'desc');
        $sortColumn = $sortField === 'createdAt' ? 'created_at' : $sortField;

        $draftsQuery->orderBy($sortColumn, $sortOrder);

        $drafts = $draftsQuery->paginate($limit, ['*'], 'page', $page);

        $data = $drafts->map(function ($draft) use ($user) {
            return [
                'id'        => $draft->id,
                'type'      => 'draft',
                'subject'   => $draft->subject,
                'message'     => $draft->message,
                'createdBy' => $user->name,
                'createdAt' => Carbon::parse($draft->created_at)->toISOString(),
                'status'    => 'unread',
                'starred'   => false,
                'replies'   => []
            ];
        });

        return $this->success($data, [
            'total'        => $drafts->total(),
            'page'         => $drafts->currentPage(),
            'limit'        => $drafts->perPage(),
            'totalPages'   => $drafts->lastPage(),
            'hasNextPage'  => $drafts->hasMorePages(),
            'hasPrevPage'  => $drafts->currentPage() > 1,
        ], $drafts->total() ? __('api.messages_retrieved_successfully') : __('api.no_messages_found'));
    }

   $query = $type === 'trash' ? Messenger::onlyTrashed(): Messenger::query();
   $query->where(function ($q) use ($user) {
        $q->where('receiver', $user->id)
          ->orWhere('sender', $user->id);
    });

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('subject', 'like', '%' . $request->search . '%')
              ->orWhere('message', 'like', '%' . $request->search . '%');
        });
    }

    if ($type === 'inbox') {
        $query->where('receiver', $user->id);
    } elseif ($type === 'outbox') {
        $query->where('sender', $user->id);
    } elseif ($type === 'starred') {
        $query->where('receiver', $user->id)->where('starred', 1);
    }

    if ($request->filled('status')) {
        $query->where('read', $request->status === 'read' ? 1 : 0);
    }

    if ($request->filled('starred')) {
        $query->where('starred', $request->boolean('starred'));
    }

    $allowedSortFields = [
      'createdAt' => 'created_at',
    ];

    $sortField = $allowedSortFields[$request->get('sort', 'createdAt')] ?? 'created_at';
    $sortOrder = in_array(strtolower($request->get('order')), ['asc', 'desc']) ? strtolower($request->get('order')) : 'desc';

    $query->orderBy($sortField, $sortOrder);

    $messages = $query->paginate($limit, ['*'], 'page', $page);

    $data = $messages->map(function ($msg) use ($user,$type) {
        return [
            'id'        => $msg->id,
            'type' => $type ?? ($msg->sender == $user->id ? 'outbox' : 'inbox'),
            'subject'   => $msg->subject,
            'message'     => $msg->message,
            'createdBy' => $msg->sender == $user->id ? $user->name : optional($msg->senderUser)->name,
            'createdAt' => $msg->created_at->toISOString(),
            'status'    => $msg->read ? 'read' : 'unread',
            'starred'   => (bool)$msg->starred,
            'replies'   => DB::table('messenger')
                ->where('reply_id', $msg->id)
                ->get()
                ->map(function ($reply) {
                    return [
                        'id'         => $reply->id,
                        'messageId'  => $reply->reply_id,
                        'subject'    => $reply->subject,
                        'content'    => $reply->message,
                        'createdBy'  => optional(User::find($reply->sender))->name,
                        'createdAt'  => Carbon::parse($reply->created_at)->toISOString(),
                        'status'     => $reply->read ? 'read' : 'unread'
                    ];
                })
        ];
    });
    $inboxCount = Messenger::where('receiver', $user->id)->count();
    $outboxCount = Messenger::where('sender', $user->id)->count();
    $starredCount = Messenger::where('receiver', $user->id)->where('starred', 1)->count();
    $trashCount = Messenger::onlyTrashed()
     ->where(function ($q) use ($user) {
        $q->where('receiver', $user->id)->orWhere('sender', $user->id);
     })->count();
    $draftCount = DB::table('message_drafts')->where('sender', $user->id)->count();

    return $this->success($data, [
        'total'        => $messages->total(),
        'page'         => $messages->currentPage(),
        'limit'        => $messages->perPage(),
        'totalPages'   => $messages->lastPage(),
        'hasNextPage'  => $messages->hasMorePages(),
        'hasPrevPage'  => $messages->currentPage() > 1,
        'inboxCount'   => $inboxCount,
        'outboxCount'  => $outboxCount,
        'starredCount' => $starredCount,
        'trashCount'   => $trashCount,
        'draftCount'   => $draftCount,
    ], __('api.messages_retrieved_successfully'));
}

/**
 * @OA\Get(
 *     path="/api/messages/{id}",
 *     summary="Get single message by ID",
 *     description="Fetch a single message with full details. Marks message as read if the current user is the receiver.",
 *     operationId="getSingleMessage",
 *     tags={"Messages"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Message ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Message retrieved successfully"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Message not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Message Not Found"),
 *             @OA\Property(property="data", type="object", example=null)
 *         )
 *     )
 * )
 */
public function show($id)
{
    $user = auth()->user();
    if (!$user->ableTo('messages.view')) {
          return $this->forbidden(__('api.forbidden'));
    }

    $message = Messenger::withTrashed()->find($id);

    if (!$message || ($message->sender !== $user->id && $message->receiver !== $user->id)) {
        return $this->error(__('api.Message_not_found'), 404);
    }

    if ($message->receiver === $user->id && !$message->read) {
        $message->read = true;
        $message->save();
    }

    $data = [
        'id'        => $message->id,
        'box'       => $message->sender == $user->id ? 'outbox' : 'inbox',
        'subject'   => $message->subject,
        'message'     => $message->message,
        'from'      => [
            'id'   =>  $message->sender,
            'name' => optional(User::find($message->sender))->first_name_en . ' ' . optional(User::find($message->sender))->last_name_en
        ],
        'to' => [
            [
                'id' => $message->receiver == $user->id ? 'usr_you' : $message->receiver,
                'name' => optional(User::find($message->receiver))->first_name_en . ' ' . optional(User::find($message->receiver))->last_name_en
            ]
        ],
        'createdAt' => Carbon::parse($message->created_at)->toISOString(),
        'updatedAt' => Carbon::parse($message->updated_at)->toISOString(),
        'isRead'    => (bool) $message->read,
        'isStarred' => (bool) $message->starred,
    ];

    return $this->success($data,[] ,__('api.message_retrieved_successfully'));
}

/**
 * @OA\Patch(
 *     path="/api/messages/trash",
 *     summary="Move messages to trash",
 *     description="Move one or more messages to the trash folder. Accepts a single ID or an array of IDs.",
 *     operationId="moveToTrash",
 *     tags={"Messages"},
 *     security={{"bearerAuth":{}}},
 * 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     type="object",
 *                     required={"messageIds"},
 *                     @OA\Property(
 *                         property="messageIds",
 *                         type="integer",
 *                         example=61,
 *                         description="Single message ID"
 *                     )
 *                 ),
 *                 @OA\Schema(
 *                     type="object",
 *                     required={"messageIds"},
 *                     @OA\Property(
 *                         property="messageIds",
 *                         type="array",
 *                         @OA\Items(type="integer"),
 *                         example={61, 62}
 *                     )
 *                 )
 *             }
 *         )
 *     ),
 *
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Partial success",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Some messages moved to trash successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="trashedCount", type="integer", example=2),
 *                 @OA\Property(property="failedIds", type="array", @OA\Items(type="integer"), example={3, 4}),
 *                 @OA\Property(property="errors", type="object", example={"3": "Message not found", "4": "Permission denied"})
 *             )
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=404,
 *         description="No accessible messages found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No accessible messages found"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="invalidIds", type="array", @OA\Items(type="integer"), example={1,2})
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation Error"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="messageIds", type="array", @OA\Items(type="string"), example={"Message IDs must be an array"})
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Authentication required")
 *         )
 *     )
 * )
 */
public function moveToTrash(MoveToTrashRequest $request)
{
   $user = auth()->user();
   if (!$user->ableTo('messages.delete')) {
          return $this->forbidden(__('api.forbidden'));
   }
    
    $messageIds = $request->messageIds;
    $trashedCount = 0;
    $failedIds = [];
    $errors = [];

    foreach ($messageIds as $id) {
        $message = Messenger::find($id);

        if (!$message) {
            $failedIds[] = $id;
            $errors[$id] = __('api.Message_not_found');
            continue;
        }

        // Only a participant may trash a message. Reported as not-found so the
        // endpoint does not confirm the existence of other users' messages.
        if ((int) $message->sender !== (int) $user->id && (int) $message->receiver !== (int) $user->id) {
            $failedIds[] = $id;
            $errors[$id] = __('api.Message_not_found');
            continue;
        }

        try {
            $message->delete();
            $trashedCount++;
        } catch (\Exception $e) {
            $failedIds[] = $id;
            $errors[$id] = __('api.Could_not_move_to_trash');
        }
    }

    if ($trashedCount === 0) {
        return $this->error(__('api.No_accessible_messages_found'), 404, [
            'invalidIds' => $failedIds
        ]);
    }

    $responseData = [
        'trashedCount' => $trashedCount,
        'failedIds'    => $failedIds,
    ];

    if (!empty($errors)) {
        $responseData['errors'] = $errors;
        return $this->success($responseData, [], __('api.Some_messages_moved_to_trash_successfully'));
    }

    return $this->success($responseData, [], __('api.Messages_moved_to_trash_successfully'));
}

/**
 * @OA\Delete(
 *     path="/api/messages",
 *     summary="Permanently delete trashed messages",
 *     description="Force deletes one or more trashed messages. Only works on messages already moved to trash.",
 *     operationId="permanentlyDeleteMessages",
 *     tags={"Messages"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     type="object",
 *                     required={"messageIds"},
 *                     @OA\Property(
 *                         property="messageIds",
 *                         type="integer",
 *                         example=55,
 *                         description="Single message ID to permanently delete"
 *                     )
 *                 ),
 *                 @OA\Schema(
 *                     type="object",
 *                     required={"messageIds"},
 *                     @OA\Property(
 *                         property="messageIds",
 *                         type="array",
 *                         @OA\Items(type="integer"),
 *                         example={55, 56, 57},
 *                         description="Array of message IDs to permanently delete"
 *                     )
 *                 )
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Messages permanently deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Messages deleted permanently"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="No accessible messages found or not trashed",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No accessible messages found"),
 *             @OA\Property(property="data", type="object", example=null)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Authentication required")
 *         )
 *     )
 * )
 */
public function forceDelete(Request $request)
{
    $user = auth()->user();

    if (!$user->ableTo('messages.delete')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $messageIds = $request->messageIds;

    if (!is_array($messageIds)) {
        $messageIds = explode(',', $messageIds);
    }

    $messageIds = array_filter(array_map('trim', $messageIds));

    $deleted = false;

    foreach ($messageIds as $id) {
        $message = Messenger::withTrashed()->find($id);

        if (!$message || !$message->trashed()) {
            continue;
        }

        if ($message->sender !== $user->id && $message->receiver !== $user->id) {
            continue;
        }

        try {
            $message->forceDelete();
            $deleted = true;
        } catch (\Exception $e) {
            // log error if needed
        }
    }

    if (!$deleted) {
        return $this->error(__('api.No_accessible_messages_found'), 404);
    }

    return $this->success(null, [], __('api.Messages_deleted_permanently'));
}
}
