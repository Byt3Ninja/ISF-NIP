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
            $this->touchPresence();

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

                // GV-7375: block sending into a DM whose peer left / was removed.
                $conversation->load('participants');
                if (!$this->conversationIsAllowed($conversation, $this->currentUserTeamContext()['teammateIds'])) {
                    return response()->json([
                        'error' => 'You can only chat with your team members'
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

            // NOTE: do NOT broadcast MessageCreated here. WireChatMessageObserver
            // already fires it on the model's "created" event (registered in
            // AppServiceProvider), which covers every creation path. Dispatching
            // again here delivered each message to Pusher twice, which is what
            // made the header notification counter jump by 2 (GV-7355).

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
     * Initials for an avatar fallback.
     * "User Name" => "UN"; single token => first two letters; empty => "?".
     */
    private function initialsFor(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '?';
        }
        $parts = preg_split('/\s+/', $name);
        if (count($parts) === 1) {
            return strtoupper(mb_substr($parts[0], 0, 2));
        }
        $first = mb_substr($parts[0], 0, 1);
        $last  = mb_substr($parts[count($parts) - 1], 0, 1);
        return strtoupper($first . $last);
    }

    /**
     * Map a User to the minimal shape the chat UI needs.
     * avatar_url is null when the user has no uploaded image (frontend renders initials);
     * never fabricate a URL — real user images resolve via User::image_handler().
     */
    private function presentUser(?User $user): ?array
    {
        if (!$user) {
            return null;
        }
        $name = trim(($user->first_name_en ?: $user->first_name_ar) . ' ' .
                     ($user->last_name_en ?: $user->last_name_ar)) ?: $user->email;
        $lastActive = $this->lastActiveForUser($user->id);
        return [
            'id'         => $user->id,
            'name'       => $name,
            'avatar_url' => $user->img_url ? User::image_handler($user->img_url) : null,
            'initials'   => $this->initialsFor($name),
            'presence'   => $this->presenceFor($lastActive),
        ];
    }

    /**
     * Coarse presence from the user's most recent activity timestamp.
     * 'active' if seen within the window, else 'offline'.
     * NOTE: this is "recently active in chat", not true real-time presence.
     */
    private function presenceFor(?\Illuminate\Support\Carbon $lastActiveAt): string
    {
        if (!$lastActiveAt) { return 'offline'; }
        return $lastActiveAt->gt(now()->subMinutes(5)) ? 'active' : 'offline';
    }

    /**
     * Most recent activity timestamp for a user across their WireChat participant
     * rows, using last_active_at. WireChat's own UI does not run on this page, so
     * this controller maintains last_active_at itself via touchPresence() — chosen
     * over conversation_read_at because that column is the unread-count watermark
     * and must not be advanced for presence.
     */
    private function lastActiveForUser(int $userId): ?\Illuminate\Support\Carbon
    {
        $ts = Participant::query()
            ->where('participantable_id', $userId)
            ->where('participantable_type', 'App\User')
            ->max('last_active_at');
        return $ts ? \Illuminate\Support\Carbon::parse($ts) : null;
    }

    /**
     * Touch the current user's activity by advancing last_active_at across their
     * participant rows. Drives the coarse presence indicator.
     *
     * IMPORTANT: call this ONLY on GENUINE user actions (sending a message, or an
     * explicit activity ping). It must NOT be called from polled endpoints
     * (messages()/getUsers()/getConversationMessages()), because the frontend polls
     * those every 15s — touching presence there would keep every open-but-idle tab
     * permanently "active", which is the bug this guard exists to prevent.
     */
    private function touchPresence(): void
    {
        try {
            $userId = auth()->id();
            if (!$userId) { return; }
            Participant::query()
                ->where('participantable_id', $userId)
                ->where('participantable_type', 'App\User')
                ->update(['last_active_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('touchPresence failed: ' . $e->getMessage());
        }
    }

    /**
     * Presence heartbeat. The frontend calls this while the chat tab is OPEN and in
     * the FOREGROUND — via a ~60s heartbeat plus immediate pings on interaction/focus,
     * throttled client-side — so "active" means the user currently has the chat open
     * and visible. It is never called from a background poll. When the tab is hidden
     * the heartbeat stops, so an away user's last_active_at ages out and they fall to
     * "offline" after the server's 5-minute window.
     */
    public function ping(): \Illuminate\Http\JsonResponse
    {
        $this->touchPresence();
        return response()->json(['ok' => true]);
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
            ->get(['id', 'name', 'group_id_chat', 'img_team']);

        return [
            'teamFormationIds' => $teamFormationIds,
            'teammateIds'      => $teammateIds,
            'teamFormations'   => $teamFormations,
        ];
    }

    /**
     * GV-7375: a private DM is only valid while BOTH users are still accepted
     * members of a shared team. When a leader removes a member, the DM row and
     * its participants survive (getOutTeam only detaches the group conversation),
     * so without this gate the removed member's chat keeps showing in the list
     * and stays readable/writable.
     *
     * Returns true when the conversation is still allowed for the current user:
     *  - group conversations: allowed (participant-ship is maintained on removal)
     *  - private conversations: allowed only if every other participant is a
     *    current accepted teammate.
     */
    private function conversationIsAllowed(Conversation $conversation, \Illuminate\Support\Collection $teammateIds): bool
    {
        // NOTE: WireChat casts `type` to the ConversationType enum, so compare on
        // the backing value rather than the raw attribute.
        $type = $conversation->type instanceof \Namu\WireChat\Enums\ConversationType
            ? $conversation->type->value
            : (string) $conversation->type;

        if ($type !== \Namu\WireChat\Enums\ConversationType::PRIVATE->value) {
            return true;
        }

        $currentUserId = auth()->id();

        $peerIds = $conversation->participants
            ->where('participantable_type', 'App\User')
            ->pluck('participantable_id')
            ->reject(fn ($id) => (int) $id === (int) $currentUserId)
            ->values();

        if ($peerIds->isEmpty()) {
            return false;
        }

        return $peerIds->every(fn ($id) => $teammateIds->contains((int) $id));
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

        // Leader user id per team (team_members.isTeamLeader = 1), for marking in chat.
        $leaderIdByTeam = Team_member::query()
            ->whereIn('team_formation_id', $teams->pluck('id'))
            ->where('acceptInvition', 'accepted')
            ->where('isTeamLeader', 1)
            ->whereNotNull('user_id')
            ->get(['team_formation_id', 'user_id'])
            ->groupBy('team_formation_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->first());

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
                    $fresh = TeamFormation::whereKey($team->id)->lockForUpdate()->first(['id', 'name', 'group_id_chat', 'img_team']);
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

            // Transient team identity for the response (not persisted on the conversation).
            // Team logos live outside the web root; expose the authorized route
            // rather than the raw storage path.
            $conversation->team_img       = $team->img_team
                ? route('hackathon.teamLogo', basename($team->img_team))
                : null;
            $conversation->team_name      = $team->name;
            $conversation->team_member_ids = $memberIds->values()->all();
            $conversation->team_leader_id = $leaderIdByTeam->get($team->id);
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
                    // Single source of truth: presentUser() also supplies `presence`,
                    // so the sidebar user rows carry the same online/offline signal as
                    // the chat header (which is enriched via presentUser too).
                    $presented        = $this->presentUser($user);
                    $user->name       = $presented['name'];
                    $user->avatar_url = $presented['avatar_url'];
                    $user->initials   = $presented['initials'];
                    $user->presence   = $presented['presence'];
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
                    // Single source of truth: presentUser() also supplies `presence`.
                    $presented        = $this->presentUser($user);
                    $user->name       = $presented['name'];
                    $user->avatar_url = $presented['avatar_url'];
                    $user->initials   = $presented['initials'];
                    $user->presence   = $presented['presence'];
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
            $me = auth()->user();

            $groups = $teamGroups->map(function ($conversation) use ($me) {
                // Participants (accepted team members) -> presenter shape.
                $memberIds = collect($conversation->team_member_ids ?? []);
                $leaderId  = $conversation->team_leader_id ?? null;
                $participants = User::whereIn('id', $memberIds)->get()
                    ->map(function ($u) use ($leaderId) {
                        $p = $this->presentUser($u);
                        if ($p) { $p['is_leader'] = ($leaderId !== null && (int) $u->id === (int) $leaderId); }
                        return $p;
                    })
                    ->filter()
                    ->values();

                // Last message preview.
                $lastMessage = $conversation->messages()->latest()->first();

                // Unread for me (best-effort).
                $unread = 0;
                try { $unread = $conversation->getUnreadCountFor($me); } catch (\Exception $e) {}

                $teamImg = $conversation->team_img ?? null;

                return [
                    'id'                 => $conversation->id,
                    'name'               => $conversation->team_name ?: $conversation->name,
                    'avatar_url'         => $teamImg ?: null,
                    'initials'           => $this->initialsFor($conversation->team_name ?: $conversation->name),
                    'participants'       => $participants,
                    'participants_count' => $participants->count(),
                    'team_leader_id'     => $leaderId,
                    'last_message'       => $lastMessage->body ?? null,
                    'unread_count'       => $unread,
                ];
            })->values();

            return response()->json([
                'groups'  => $groups,
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

            // GV-7375: drop DMs whose peer is no longer an accepted teammate, so a
            // removed member's conversation disappears from the list immediately.
            $teammateIds = $this->currentUserTeamContext()['teammateIds'];
            $conversations = $conversations
                ->filter(fn ($conversation) => $this->conversationIsAllowed($conversation, $teammateIds))
                ->values();

            $conversations->each(function ($conversation) use ($user) {
                // Preserve the eager-loaded last message (limit(1)) for the sidebar preview,
                // then UNSET the truncated `messages` relation before counting unread.
                // WireChat's getUnreadCountFor()/unreadMessages() filters the *already-loaded*
                // messages collection when the relation is loaded — so a limit(1) eager-load
                // caps the unread count at 1. Unsetting it forces the correct fresh COUNT query.
                $lastMessage = $conversation->relationLoaded('messages')
                    ? $conversation->getRelation('messages')->first()
                    : null;
                $conversation->unsetRelation('messages');

                try {
                    $conversation->unread_count = $conversation->getUnreadCountFor($user);
                } catch (\Exception $e) {
                    $conversation->unread_count = 0;
                }

                // Re-attach only the last message (as a 1-item collection) for the JSON preview.
                $conversation->setRelation(
                    'messages',
                    $lastMessage ? collect([$lastMessage]) : collect()
                );
                foreach ($conversation->participants as $participant) {
                    if ($participant->participantable instanceof User) {
                        $presented = $this->presentUser($participant->participantable);
                        $participant->participantable->avatar_url = $presented['avatar_url'];
                        $participant->participantable->initials   = $presented['initials'];
                        $participant->participantable->name       = $presented['name'];
                        $participant->participantable->presence   = $presented['presence'];
                    }
                }
            });

            return response()->json([
                'conversations' => $conversations,
                'user_id'       => auth()->id()
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

            // GV-7375: participant-ship alone is not enough — a DM with a removed
            // teammate must not be re-openable (the client restores the last
            // conversation id from localStorage).
            $conversation->load('participants');
            if (!$this->conversationIsAllowed($conversation, $this->currentUserTeamContext()['teammateIds'])) {
                return response()->json([
                    'error' => 'You can only chat with your team members',
                    'messages' => []
                ], 403);
            }

            // Opening a conversation = reading it. Best-effort: never fail loading.
            try {
                $conversation->markAsRead(auth()->user());
            } catch (\Exception $e) {
                Log::warning('markAsRead failed for conversation ' . $conversation->id . ': ' . $e->getMessage());
            }

            $messages = $conversation->messages()
                ->with('sendable')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    $presented = $message->sendable instanceof User
                        ? $this->presentUser($message->sendable)
                        : null;
                    // Expose avatar fields alongside the existing sendable payload.
                    $message->sender_avatar_url = $presented['avatar_url'] ?? null;
                    $message->sender_initials   = $presented['initials'] ?? '?';
                    $message->sender_name       = $presented['name'] ?? 'User';
                    return $message;
                });

            $unreadCount = 0; // just marked read
            try {
                $unreadCount = $conversation->getUnreadCountFor(auth()->user());
            } catch (\Exception $e) {
                Log::warning('getUnreadCountFor failed: ' . $e->getMessage());
            }

            $conversation->load('participants.participantable');
            foreach ($conversation->participants as $participant) {
                if ($participant->participantable instanceof User) {
                    $p = $this->presentUser($participant->participantable);
                    $participant->participantable->avatar_url = $p['avatar_url'];
                    $participant->participantable->initials   = $p['initials'];
                    $participant->participantable->name       = $p['name'];
                    $participant->participantable->presence   = $p['presence'];
                }
            }

            return response()->json([
                'messages'     => $messages,
                'conversation' => $conversation,
                'unread_count' => $unreadCount,
                'user_id'      => auth()->id()
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

            $enrich = function ($conversation) {
                $conversation->load('participants.participantable');
                foreach ($conversation->participants as $participant) {
                    if ($participant->participantable instanceof User) {
                        $p = $this->presentUser($participant->participantable);
                        $participant->participantable->avatar_url = $p['avatar_url'];
                        $participant->participantable->initials   = $p['initials'];
                        $participant->participantable->name       = $p['name'];
                        $participant->participantable->presence   = $p['presence'];
                    }
                }
                return $conversation;
            };

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
                        'conversation' => $enrich($existingConversation),
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
                'conversation' => $enrich($conversation),
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
