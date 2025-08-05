<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoList extends Model
{
    use HasFactory;
     protected $fillable = [
        'title',
        'status',
        'assignee',
        'due_date',
        'cycle_id',
        'step_id',
        'user_id',
        'team_formation_id',
        'team_member_id',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function teamFormation()
    {
        return $this->belongsTo(TeamFormation::class);
    }

    public function teamMember()
    {
        return $this->belongsTo(TeamMember::class);
    }
}
