<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messenger extends Model
{
    protected $fillable = [
        'subject',
        'message',
        'receiver',
        'sender',
        'messgeId',
        'read',
        'reported',
        'starred',
    ];
    protected $table = 'messenger';
    use SoftDeletes;


    public function senderUser() {
        return $this->belongsTo(User::class, 'sender');
    }
    
    public function receiverUser() {
        return $this->belongsTo(User::class, 'receiver');
    }

    // الرسائل التي تم الرد بها على هذه الرسالة
    public function replies()
    {
        return $this->belongsToMany(Messenger::class, 'message_replies', 'messenger_id', 'reply_id');
    }

    // الرسالة التي تم الرد عليها
    public function repliedTo()
    {
        return $this->belongsToMany(Messenger::class, 'message_replies', 'reply_id', 'messenger_id');
    }
}
