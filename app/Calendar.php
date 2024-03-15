<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Calendar extends Model
{
    use Auditable;
    public function user() {
        return $this->belongsTo(User::class);
    }
    protected $fillable = ['organization_id', 'title', 'start', 'end','body','user_id','color','allDay','isPrivate', ];
    protected $casts = [
        'allDay' => 'boolean','isPrivate'=> 'boolean',
    ];
}
