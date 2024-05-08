<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Calendar extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'title', 'start', 'end','body','user_id','color','allDay','isPrivate', ];
    protected $casts = [
        'allDay' => 'boolean','isPrivate'=> 'boolean',
    ];

    // ========> Attributes <==========
    public function getStartAttribute()
    {

        return $this->start->format('Y-m-d h:i a');
    }
    public function getEndAttribute()
    {

        return $this->end->format('Y-m-d h:i a');
    }

    // ==============> Relations <==============
    public function user() {
        return $this->belongsTo(User::class);
    }
}
