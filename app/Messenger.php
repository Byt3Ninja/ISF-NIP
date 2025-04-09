<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messenger extends Model
{
    protected $table = 'messenger';
    use SoftDeletes;


    public function senderUser() {
        return $this->belongsTo(User::class, 'sender');
    }
    
    public function receiverUser() {
        return $this->belongsTo(User::class, 'receiver');
    }
}
