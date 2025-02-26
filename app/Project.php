<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function programs()
    {
        return $this->belongsToMany(Cycle::class, 'cycle_project')->withPivot(['status','created_at']);
    }

    public function teamMembers() 
    {
        return $this->belongsToMany(Team_member::class, 'project_team_member')->withPivot(['role','created_at'])->withTimestamps();
    }
}
