<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Messenger;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleStarRequest;
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
        $isDraft  = $request->draft;

       if ($isDraft) {
        $draftId = DB::table('message_drafts')->insertGetId([
            'sender'    => $senderId,
            'receiver'  => $request->userId,
            'cycle_id'  => $request->cycleId,
            'subject'   => $request->subject,
            'message'   => $request->title,
            'drafts'    => 1,
            'created_at'=> now(),
            'updated_at'=> now()
        ]);

        $data = [
            'id'        => $draftId,
            'type'      => 'draft',
            'title'     => $request->title,
            'subject'   => $request->subject,
            'createdBy' => $sender->name,
            'createdAt' => now()->toISOString(),
            'status'    => 'unread',
            'starred'   => false
        ];

        return $this->created($data, __('api.Draft_saved_successfully'));
       }

      $recipients = collect();

      if ($request->userTypeValue == 1) {
        $recipients = User::whereHas('company')
            ->when($request->userId, function ($q) use ($request) {
                $q->whereIn('id', $request->userId);
            })
            ->get();

      } elseif ($request->userTypeValue == 2) {
        $recipients = User::whereIn('id', $request->userId ?? [])->get();

      } elseif ($request->userTypeValue == 3) {
        $recipients = User::whereIn('organization_id', $request->userId ?? [])->get();

      } else {
        $recipients = User::where('organization_id', auth()->user()->organization_id)
        ->get();
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
            'message'   => $request->title,
            'read'      => 0,
            'starred'   => 0,
            'created_at'=> now(),
            'updated_at'=> now()
        ]);

        $insertedMessages[] = [
            'id'        => $messageId,
            'type'      => 'outbox',
            'title'     => $request->title,
            'subject'   => $request->subject,
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
        $originalMessage = DB::table('messenger')
        ->where('id', $request->messageId)
        ->first();

      if (!$originalMessage) {
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
      $ids = $request->messageIds;

      if (!is_array($ids)) {
        $ids = explode(',', $ids);
      }

     $ids = array_map('trim', $ids);

     $statusValue = $request->status === 'read' ? 1 : 0;
     Messenger::whereIn('id', $ids)->update(['read' => $statusValue]);

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
        $ids = is_array($request->messageIds) ? $request->messageIds : explode(',', $request->messageIds);

        Messenger::whereIn('id', $ids)->update([
              'starred' => $request->starred === true ? 1 : 0
         ]);
        return $this->success(null, [], __('api.Message_star_status_updated'));
    }
}
