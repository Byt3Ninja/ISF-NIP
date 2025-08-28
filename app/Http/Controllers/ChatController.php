<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Namu\WireChat\Enums\Actions;
use Namu\WireChat\Models\Message;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Participant;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('chat/index');
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

        try {
            // Create group conversation
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $request->name,
                'description' => $request->description
            ]);

            // Add current user as admin
            $conversation->participants()->create([
                'participantable_id' => auth()->id(),
                'participantable_type' => get_class(auth()->user()),
                'role' => 'admin'
            ]);

            return response()->json(['group' => $conversation->load('participants')], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create group: ' . $e->getMessage()
            ], 500);
        }
    }

    // إضافة عضو
    public function addUser(Request $request, $groupId)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $conversation = Conversation::findOrFail($groupId);
            $user = User::findOrFail($request->user_id);

            // Check if user is already a participant
            $existingParticipant = $conversation->participants()
                ->where('participantable_id', $user->id)
                ->where('participantable_type', get_class($user))
                ->exists();

            if ($existingParticipant) {
                return response()->json([
                    'error' => 'User is already in this conversation'
                ], 400);
            }

            // Add user as participant
            $conversation->participants()->create([
                'participantable_id' => $user->id,
                'participantable_type' => get_class($user),
                'role' => 'participant'
            ]);

            return response()->json(['message' => 'User added successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeUser(Request $request, $groupId)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $conversation = Conversation::findOrFail($groupId);
            $user = User::findOrFail($request->user_id);
            
            $participant = $conversation->participants()
                ->where('participantable_id', $user->id)
                ->where('participantable_type', get_class($user))
                ->first();
                
            if (!$participant) {
                return response()->json([
                    'error' => 'User is not in this conversation'
                ], 404);
            }

            $participant->delete();
            
            return response()->json([
                'message' => "User {$user->name} removed from conversation successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to remove user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exitGroup($groupId)
    {
        try {
            $conversation = Conversation::findOrFail($groupId);
            
            // Remove user from conversation participants
            $conversation->participants()
                ->where('participantable_id', auth()->id())
                ->where('participantable_type', get_class(auth()->user()))
                ->delete();

            return response()->json(['message' => 'You have exited the group']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to exit group: ' . $e->getMessage()
            ], 500);
        }
    }

    // مسح الجروب
    public function deleteGroup($groupId)
    {
        $conversation = Conversation::findOrFail($groupId);
        
        $conversation->deleteFor(auth()->user());

        return response()->json(['message' => 'Group deleted']);
    }

    // إرسال رسالة
    public function sendMessage(Request $request, $conversationId = null)
    {
        try {
            $request->validate([
                'message' => 'required|string',
                'conversation_id' => 'required_without:user_id|exists:wire_conversations,id',
                'user_id' => 'required_without:conversation_id|exists:users,id'
            ]);

            $user = auth()->user();
            
            // If conversation_id is provided, use it. Otherwise create or get conversation with user_id
            if ($request->conversation_id) {
                $conversation = Conversation::findOrFail($request->conversation_id);
                
                // Check if user is participant
                $isParticipant = $conversation->participants()
                    ->where('participantable_id', $user->id)
                    ->where('participantable_type', get_class($user))
                    ->exists();
                    
                if (!$isParticipant) {
                    return response()->json([
                        'error' => 'You are not a participant in this conversation'
                    ], 403);
                }
            } elseif ($request->user_id) {
                // Create or get conversation with the specified user
                $createConversationRequest = new Request(['user_id' => $request->user_id]);
                $conversationResponse = $this->createOrGetConversation($createConversationRequest);
                $conversationData = json_decode($conversationResponse->getContent(), true);
                
                if ($conversationResponse->getStatusCode() !== 200) {
                    return response()->json([
                        'error' => 'Failed to create or get conversation'
                    ], 500);
                }
                
                $conversation = Conversation::find($conversationData['conversation']['id']);
            }

            // Create the message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sendable_id' => $user->id,
                'sendable_type' => get_class($user),
                'body' => $request->message,
                'type' => 'text'
            ]);

            // Load the message with sender information
            $message->load('sendable');

            // Broadcast the message to conversation participants (real-time feature)
            try {
                Log::info('Attempting to dispatch BroadcastMessage job for message: ' . $message->id);
                \Namu\WireChat\Jobs\BroadcastMessage::dispatch($message);
                Log::info('BroadcastMessage job dispatched successfully for message: ' . $message->id);
            } catch (\Exception $e) {
                // Log broadcasting error but don't fail the message sending
                Log::error('Failed to broadcast message: ' . $e->getMessage());
            }

            return response()->json([
                'message' => $message,
                'conversation' => $conversation->load('participants.participantable'),
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error sending message: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
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
        try {
            $query = $request->get('q', '');
            $currentUserId = auth()->id();
            
            if (strlen($query) < 2) {
                return response()->json(['users' => []]);
            }
            
            $users = User::where('id', '!=', $currentUserId)
                ->where(function($q) use ($query) {
                    $q->where('first_name_en', 'like', "%$query%")
                      ->orWhere('last_name_en', 'like', "%$query%")
                      ->orWhere('first_name_ar', 'like', "%$query%")
                      ->orWhere('last_name_ar', 'like', "%$query%")
                      ->orWhere('email', 'like', "%$query%")
                      ->orWhereRaw("CONCAT(first_name_en, ' ', last_name_en) LIKE ?", ["%$query%"])
                      ->orWhereRaw("CONCAT(first_name_ar, ' ', last_name_ar) LIKE ?", ["%$query%"]);
                })
                ->select('id', 'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'email', 'img_url')
                ->limit(10)
                ->get()
                ->map(function($user) {
                    // Create a combined name field
                    $user->name = trim(($user->first_name_en ?: $user->first_name_ar) . ' ' . ($user->last_name_en ?: $user->last_name_ar)) ?: $user->email;
                    return $user;
                });

            return response()->json(['users' => $users]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error searching users: ' . $e->getMessage(),
                'users' => []
            ], 500);
        }
    }
    public function myGroups()
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'groups' => []
                ], 401);
            }

            // Get groups using the Chatable trait method
            $user = auth()->user();
            $groups = $user->groups ?? collect();

            return response()->json([
                'groups' => $groups,
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching groups: ' . $e->getMessage(),
                'groups' => []
            ], 500);
        }
    }

    public function messages()
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'messages' => []
                ], 401);
            }

            // Get conversations the user is part of via participants table
            $user = auth()->user();
            $conversations = Conversation::whereHas('participants', function($query) use ($user) {
                $query->where('participantable_id', $user->id)
                      ->where('participantable_type', get_class($user));
            })->with(['participants.participantable', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])->get();

            return response()->json([
                'conversations' => $conversations,
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching messages: ' . $e->getMessage(),
                'conversations' => []
            ], 500);
        }
    }

    // Get messages for a specific conversation
    public function getConversationMessages($conversationId)
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'messages' => []
                ], 401);
            }

            $conversation = Conversation::findOrFail($conversationId);
            
            // Check if user is participant
            $isParticipant = $conversation->participants()
                ->where('participantable_id', auth()->id())
                ->where('participantable_type', get_class(auth()->user()))
                ->exists();
                
            if (!$isParticipant) {
                return response()->json([
                    'error' => 'You are not a participant in this conversation',
                    'messages' => []
                ], 403);
            }

            $messages = $conversation->messages()
                ->with('sendable')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'messages' => $messages,
                'conversation' => $conversation,
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching conversation messages: ' . $e->getMessage(),
                'messages' => []
            ], 500);
        }
    }

    // Create or get existing conversation with a user
    public function createOrGetConversation(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'type' => 'in:private,group'
            ]);

            $otherUserId = $request->user_id;
            $currentUserId = auth()->id();
            $type = $request->type ?? 'private';

            if ($otherUserId == $currentUserId) {
                return response()->json([
                    'error' => 'Cannot create conversation with yourself'
                ], 400);
            }

            if ($type === 'private') {
                // Check if private conversation already exists between these two users
                $existingConversation = Conversation::where('type', 'private')
                    ->whereHas('participants', function($query) use ($currentUserId) {
                        $query->where('participantable_id', $currentUserId)
                              ->where('participantable_type', 'App\User');
                    })
                    ->whereHas('participants', function($query) use ($otherUserId) {
                        $query->where('participantable_id', $otherUserId)
                              ->where('participantable_type', 'App\User');
                    })
                    ->first();

                if ($existingConversation) {
                    return response()->json([
                        'conversation' => $existingConversation->load('participants.participantable'),
                        'existing' => true
                    ]);
                }

                // Create new private conversation
                $conversation = new Conversation();
                $conversation->type = 'private';
                $conversation->save();

                // Add both users as participants
                $conversation->participants()->create([
                    'participantable_id' => $currentUserId,
                    'participantable_type' => 'App\User',
                    'role' => 'participant'
                ]);

                $conversation->participants()->create([
                    'participantable_id' => $otherUserId,
                    'participantable_type' => 'App\User',
                    'role' => 'participant'
                ]);
            } else {
                // For group conversations, you might want different logic
                return response()->json([
                    'error' => 'Group conversation creation not implemented in this endpoint'
                ], 400);
            }

            return response()->json([
                'conversation' => $conversation->load('participants.participantable'),
                'existing' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error creating conversation: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get a specific message by ID (for real-time broadcasting)
    public function getMessage($messageId)
    {
        try {
            $message = Message::with(['sendable', 'conversation'])
                ->findOrFail($messageId);
            
            // Check if user has access to this message's conversation
            $hasAccess = $message->conversation->participants()
                ->where('participantable_id', auth()->id())
                ->where('participantable_type', get_class(auth()->user()))
                ->exists();
                
            if (!$hasAccess) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'body' => $message->body,
                    'created_at' => $message->created_at,
                    'sendable_id' => $message->sendable_id,
                    'sendable' => [
                        'id' => $message->sendable->id,
                        'name' => $message->sendable->name ?? $message->sendable->email ?? 'Unknown'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Message not found: ' . $e->getMessage()
            ], 404);
        }
    }
    
}
