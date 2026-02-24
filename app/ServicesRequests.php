<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class ServicesRequests extends Model
{
    use Auditable;

    protected $table = 'services_requests';
    protected $fillable = ['organization_id', 'service_id','user_id','comment', 'approved', 'admin_comment','date_time','rate','rate_comment','done','decided_at','review_title','review_body','reviewed_at'];
    protected $casts = ['date_time'  => 'date:Y-m-d H:i', 'decided_at' => 'datetime', 'reviewed_at' => 'datetime',];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class,'service_id','id');
    }

    public function statusLogs()
    {
        return $this->hasMany(ServiceRequestStatusLog::class, 'service_request_id')
                    ->orderBy('occurred_at', 'asc');
    }
}
