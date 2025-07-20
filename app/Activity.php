<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
        'browser',
        'device',
        'location',
        'ip_address',
        'platform',
        'activity_time',
    ];

    protected $casts = [
        'activity_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
