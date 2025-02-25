<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team_member extends Model
{
    protected $table = 'team_members';

    public function projects() 
    {
        return $this->belongsToMany(Project::class, 'project_team_member') ->withPivot(['role','created_at'])->withTimestamps();
    }
    use HasFactory;
}
