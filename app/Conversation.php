<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Namu\WireChat\Models\Conversation as BaseConversation;

class Conversation extends BaseConversation
{
 
protected $fillable = [
    'type',
    'disappearing_started_at',
    'disappearing_duration',
];

}

