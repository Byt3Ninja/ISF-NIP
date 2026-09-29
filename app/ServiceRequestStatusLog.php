<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestStatusLog extends Model
{
    protected $table = 'service_request_status_logs';

    protected $fillable = [
        'service_request_id',
        'status',
        'comment',
        'causer_id',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServicesRequests::class, 'service_request_id');
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
