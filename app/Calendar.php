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
        'start'=>'datetime' , 'end'=>'datetime'
    ];

    // ========> Attributes <==========
    public function getStartAttribute()
    {

        return date('Y-m-d h:i a',strtotime($this->attributes['start']));
    }
    public function getEndAttribute()
    {

        return date('Y-m-d h:i a',strtotime($this->attributes['end']));
    }

    // ==============> Relations <==============
    public function user() {
        return $this->belongsTo(User::class);
    }
}
