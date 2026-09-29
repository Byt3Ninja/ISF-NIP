<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('Web-Message', function ($user) {
    return $user !== null;
});

// Chat conversation channels
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Check if user is a participant in this conversation
    $participant = \Namu\WireChat\Models\Participant::where('conversation_id', $conversationId)
        ->where('participantable_type', get_class($user))
        ->where('participantable_id', $user->id)
        ->first();
    
    return $participant !== null;
});
