<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'mentorship_request_id',
        'recording_type',
        'recording_id',
        'recording_view_url',
        'recording_download_url',
        'recording_time',
        'passcode',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }
}
