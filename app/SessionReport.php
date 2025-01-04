<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'participant_user_id',
        'participant_email',
        'participant_name',
        'participant_join_date',
        'participant_end_date',
        'attendance_duration',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }
}
