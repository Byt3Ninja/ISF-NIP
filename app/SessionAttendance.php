<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionAttendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'session_id',
        'user_id',
        'attended',
        'joined_at',
    ];

    // علاقة مع الجلسة
    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }

    // علاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}
