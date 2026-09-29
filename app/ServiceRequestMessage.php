<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestMessage extends Model
{
    protected $table = 'service_request_messages';

    protected $fillable = [
        'service_request_id',
        'sender_id',
        'body',
        'attachment_path',
        'attachment_name',
        'read_at',
        'emailed_at',
    ];

    protected $casts = [
        'read_at'   => 'datetime',
        'emailed_at' => 'datetime',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServicesRequests::class, 'service_request_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
