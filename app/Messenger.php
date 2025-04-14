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
        'reply_id',
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
    public function reply()
    {
        return $this->belongsTo(Messenger::class, 'reply_id');
    }
    
}
