<?php

namespace App\Http\Controllers;

use App\User;
use App\Team_member;
use App\TeamFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            if (!$this->callerIsGroupAdmin($conversation)) {
                return response()->json(['error' => 'Access denied'], 403);
            }
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
            if (!$this->callerIsGroupAdmin($conversation)) {
                return response()->json(['error' => 'Access denied'], 403);
            }
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
        if (!$this->callerIsGroupAdmin($conversation)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

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

            // SECURITY: sender identity is ALWAYS taken from auth()->user(); the
            // client cannot supply/override sendable_id or sendable_type. Do not change.
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sendable_id' => $user->id,
                'sendable_type' => get_class($user),
                'body' => $request->message,
                'type' => 'text'
            ]);

            // Load the message with sender information
            $message->load('sendable');

            // Broadcast the message synchronously so real-time works without a queue worker.
            // App\Events\MessageCreated is ShouldBroadcastNow (fires immediately to Pusher)
            // on PrivateChannel('conversation.'.$id) with the full message payload.
            try {
                broadcast(new \App\Events\MessageCreated($message));
                Log::info('MessageCreated broadcast (sync) for message: ' . $message->id);
            } catch (\Exception $e) {
                // Never fail the send if broadcasting fails.
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

        // Authorization: only the original sender may delete their message.
        if ((int) $message->sendable_id !== (int) auth()->id()
            || $message->sendable_type !== get_class(auth()->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            event(new \Namu\WireChat\Events\MessageDeleted($message));
            Log::info('MessageDeleted event fired for message: ' . $message->id);
        } catch (\Exception $e) {
            Log::error('Failed to fire MessageDeleted event: ' . $e->getMessage());
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted']);
    }

    /**
     * True if the current user is an admin-role participant of the conversation.
     */
    private function callerIsGroupAdmin(Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('participantable_id', auth()->id())
            ->where('participantable_type', get_class(auth()->user()))
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Single source of truth for the current user's hackathon-team context.
     *
     * Returns, unioned across ALL teams the user is an ACCEPTED member of:
     *  - teamFormationIds: ids of those teams
     *  - teammateIds:      accepted co-members' user ids, excluding self
     *  - teamFormations:   the TeamFormation models (for group get-or-create)
     *
     * Computed in 3 queries max, no N+1: id-only plucks + one whereIn hydrate.
     */
    private function currentUserTeamContext(): array
    {
        $currentUserId = auth()->id();

        // Query 1: teams where I am an accepted member (ids only).
        $teamFormationIds = Team_member::query()
            ->where('user_id', $currentUserId)
            ->where('acceptInvition', 'accepted')
            ->pluck('team_formation_id')
            ->unique()
            ->values();

        if ($teamFormationIds->isEmpty()) {
            return [
                'teamFormationIds' => collect(),
                'teammateIds'      => collect(),
                'teamFormations'   => collect(),
            ];
        }

        // Query 2: accepted co-members across those teams (user ids only), minus self.
        $teammateIds = Team_member::query()
            ->whereIn('team_formation_id', $teamFormationIds)
            ->where('acceptInvition', 'accepted')
            ->where('user_id', '!=', $currentUserId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->values();

        // Query 3: hydrate the team rows once (for group get-or-create).
        $teamFormations = TeamFormation::query()
            ->whereIn('id', $teamFormationIds)
            ->get(['id', 'name', 'group_id_chat']);

        return [
            'teamFormationIds' => $teamFormationIds,
            'teammateIds'      => $teammateIds,
            'teamFormations'   => $teamFormations,
        ];
    }

    /**
     * Get-or-create one WireChat group conversation per team the current user
     * belongs to that has >= 2 accepted members. Persists the conversation id on
     * team_formations.group_id_chat. Ensures all accepted teammates are participants.
     *
     * Batched: candidate conversations loaded in one whereIn; participants eager
     * loaded; participant inserts batched. Creation guarded by a transaction +
     * re-check to avoid duplicate groups under concurrent access.
     */
    private function ensureTeamGroups(): \Illuminate\Support\Collection
    {
        $currentUserId = auth()->id();
        $context = $this->currentUserTeamContext();
        /** @var \Illuminate\Support\Collection $teams */
        $teams = $context['teamFormations'];

        if ($teams->isEmpty()) {
            return collect();
        }

        // Accepted members per team (one query, grouped) — used for the >=2 gate
        // and to build participant sets without a query per team.
        $membersByTeam = Team_member::query()
            ->whereIn('team_formation_id', $teams->pluck('id'))
            ->where('acceptInvition', 'accepted')
            ->whereNotNull('user_id')
            ->get(['team_formation_id', 'user_id'])
            ->groupBy('team_formation_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->unique()->values());

        // Batch-load existing conversations by their stored group ids (one query).
        $existingIds = $teams->pluck('group_id_chat')->filter()->values();
        $existingConversations = $existingIds->isNotEmpty()
            ? Conversation::whereIn('id', $existingIds)->with('participants')->get()->keyBy('id')
            : collect();

        $result = collect();

        foreach ($teams as $team) {
            $memberIds = $membersByTeam->get($team->id, collect());
            if ($memberIds->count() < 2) {
                continue; // no group until a second member joins
            }

            $conversation = $team->group_id_chat
                ? $existingConversations->get($team->group_id_chat)
                : null;

            // Create if missing or dangling, guarded against races.
            if (!$conversation) {
                $conversation = DB::transaction(function () use ($team, $memberIds) {
                    // Re-read the team row under lock to re-check group_id_chat.
                    $fresh = TeamFormation::whereKey($team->id)->lockForUpdate()->first(['id', 'name', 'group_id_chat']);
                    if ($fresh->group_id_chat) {
                        $existing = Conversation::with('participants')->find($fresh->group_id_chat);
                        if ($existing) {
                            return $existing;
                        }
                    }

                    $conv = Conversation::create([
                        'type' => 'group',
                        'name' => $fresh->name ?: ('Team #' . $fresh->id),
                    ]);

                    TeamFormation::whereKey($fresh->id)->update(['group_id_chat' => $conv->id]);

                    return $conv->load('participants');
                });
            }

            // Ensure every accepted member is a participant (no query per member).
            $existingParticipantIds = $conversation->participants
                ->where('participantable_type', 'App\User')
                ->pluck('participantable_id')
                ->all();

            $missing = $memberIds->reject(fn ($id) => in_array($id, $existingParticipantIds))->values();

            if ($missing->isNotEmpty()) {
                $rows = $missing->map(fn ($id) => [
                    'participantable_id'   => $id,
                    'participantable_type' => 'App\User',
                    'role'                 => $id === $currentUserId ? 'admin' : 'participant',
                ])->all();

                $conversation->participants()->createMany($rows); // single batched insert via Eloquent
                $conversation->load('participants');               // refresh once
            }

            $result->push($conversation);
        }

        return $result;
    }

    // Get list of users that can be chatted with
    public function getUsers(Request $request)
    {
        try {
            $context     = $this->currentUserTeamContext();
            $teammateIds = $context['teammateIds'];
            $hasTeam     = $context['teamFormationIds']->isNotEmpty();

            if ($teammateIds->isEmpty()) {
                return response()->json(['users' => [], 'total' => 0, 'has_team' => $hasTeam]);
            }

            $users = User::whereIn('id', $teammateIds)
                ->select('id', 'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'email', 'img_url')
                ->get()
                ->map(function ($user) {
                    $user->name = trim(($user->first_name_en ?: $user->first_name_ar) . ' ' .
                        ($user->last_name_en ?: $user->last_name_ar)) ?: $user->email;
                    return $user;
                });

            return response()->json([
                'users'    => $users,
                'total'    => $users->count(),
                'has_team' => $hasTeam,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching users: ' . $e->getMessage(), 'users' => []], 500);
        }
    }

    public function searchUser(Request $request)
    {
        try {
            $q           = $request->get('q', '');
            $context     = $this->currentUserTeamContext();
            $teammateIds = $context['teammateIds'];
            $hasTeam     = $context['teamFormationIds']->isNotEmpty();

            if ($teammateIds->isEmpty()) {
                return response()->json(['users' => [], 'total' => 0, 'has_team' => $hasTeam]);
            }

            $query = User::whereIn('id', $teammateIds);
            if ($q !== '') {
                $query->where(function ($builder) use ($q) {
                    $builder->where('first_name_en', 'like', '%' . $q . '%')
                        ->orWhere('last_name_en', 'like', '%' . $q . '%')
                        ->orWhere('first_name_ar', 'like', '%' . $q . '%')
                        ->orWhere('last_name_ar', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                });
            }

            $users = $query->select('id', 'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'email', 'img_url')
                ->get()
                ->map(function ($user) {
                    $user->name = trim(($user->first_name_en ?: $user->first_name_ar) . ' ' .
                        ($user->last_name_en ?: $user->last_name_ar)) ?: $user->email;
                    return $user;
                });

            return response()->json(['users' => $users, 'total' => $users->count(), 'has_team' => $hasTeam]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error searching users: ' . $e->getMessage(), 'users' => []], 500);
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

            $teamGroups = $this->ensureTeamGroups();

            return response()->json([
                'groups'  => $teamGroups->values(),
                'user_id' => auth()->id(),
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
                'conversation' => $conversation->load('participants.participantable'),
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

            // SECURITY (GV-3813): private DMs are restricted to accepted teammates.
            // Prevents crafting a DM with an arbitrary platform user by posting a raw user_id.
            $teammateIds = $this->currentUserTeamContext()['teammateIds'];
            if (!$teammateIds->contains((int) $otherUserId)) {
                return response()->json([
                    'error' => 'You can only chat with your team members'
                ], 403);
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
    
    // Handle typing indicator
    public function typing(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:wire_conversations,id',
                'is_typing' => 'required|boolean'
            ]);

            $user = auth()->user();
            $conversation = Conversation::findOrFail($request->conversation_id);

            // Check if user is participant in conversation
            $isParticipant = $conversation->participants()
                ->where('participantable_id', $user->id)
                ->where('participantable_type', get_class($user))
                ->exists();

            if (!$isParticipant) {
                return response()->json([
                    'error' => 'You are not a participant in this conversation'
                ], 403);
            }

            // Broadcast typing indicator event (no database saving needed)
            if ($request->is_typing) {
                event(new \App\Events\TypingIndicator($user, $conversation->id, true));
                Log::info('TypingIndicator START event broadcast for user: ' . $user->id . ' in conversation: ' . $conversation->id);
            } else {
                event(new \App\Events\TypingIndicator($user, $conversation->id, false));
                Log::info('TypingIndicator STOP event broadcast for user: ' . $user->id . ' in conversation: ' . $conversation->id);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error handling typing indicator: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error handling typing indicator: ' . $e->getMessage()
            ], 500);
        }
    }
}
