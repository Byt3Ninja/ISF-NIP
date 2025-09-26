<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipSchedule extends Model
{
    use HasFactory;

    protected $table = 'mentorship_schedules';

    protected $fillable = [
        'value',
        'unit',
        'is_active',
        'organization_id',
        'cycle_id',
        'created_at',
        'updated_at',
    ];

     public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }


}
