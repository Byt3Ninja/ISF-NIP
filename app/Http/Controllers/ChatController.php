<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Namu\WireChat\Enums\Actions;
use Namu\WireChat\Models\Message;
use Namu\WireChat\Models\Conversation;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // إنشاء جروب
    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image'
        ]);

        $conversation = auth()->user()->createGroup(
            name: $request->name,
            description: $request->description,
            photo: $request->file('photo')
        );

        return response()->json(['group' => $conversation], 201);
    }

    // إضافة عضو
    public function addUser(Request $request, $groupId)
    {
        $conversation = Conversation::findOrFail($groupId);
        $user = User::findOrFail($request->user_id);

        $conversation->addParticipant($user);

        return response()->json(['message' => 'User added successfully']);
    }
    public function removeUser(Request $request, $groupId)
    {
        $group = Conversation::findOrFail($groupId);
        $user  = \App\User::findOrFail($request->user_id);
        $participant = $group->participants()->where('participantable_id', $user->id)->first();
        if (! $participant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'المستخدم مش موجود فى الجروب'
            ], 404);
        }
        $participant->removeByAdmin(auth()->user());
        return response()->json([
            'status'  => 'success',
            'message' => "تم إزالة {$user->name} من الجروب {$group->name}"
        ]);
    }

    public function exitGroup($groupId)
    {
        $conversation = Conversation::findOrFail($groupId);
        auth()->user()->exitConversation($conversation);

        return response()->json(['message' => 'You have exited the group']);
    }

    // مسح الجروب
    public function deleteGroup($groupId)
    {
        $conversation = Conversation::findOrFail($groupId);
        
        $conversation->deleteFor(auth()->user());

        return response()->json(['message' => 'Group deleted']);
    }

    // إرسال رسالة
    public function sendMessage(Request $request, $groupId)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $conversation = Conversation::findOrFail($groupId);
        $message = auth()->user()->sendMessageTo($conversation, $request->message);

        return response()->json(['message' => $message]);
    }

    // مسح رسالة
    public function deleteMessage($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->delete();

        return response()->json(['message' => 'Message deleted']);
    }

    // البحث عن مستخدم
    public function searchUser(Request $request)
    {
        $query = $request->get('q', '');
        $users = User::where('name', 'like', "%$query%")
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['users' => $users]);
    }
    public function myGroups()
    {
        $groups = auth()->user()->groups;

    return response()->json([
        'groups' => $groups
    ]);
    }

    public function messages()
    {
        $messages = auth()->user()->messages;

    return response()->json([
        'messages' => $messages
    ]);
    }
    

}
