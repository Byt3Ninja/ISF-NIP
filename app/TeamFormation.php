<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamFormation extends Model
{
    use HasFactory;
    protected $table = 'team_formations';

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }
    public function step()
    {
        return $this->belongsTo(Step::class);
    }
    public function teamMembers()
    {
        return $this->hasMany(Team_member::class, 'team_formation_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
