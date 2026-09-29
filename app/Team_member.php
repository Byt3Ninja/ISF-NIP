<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team_member extends Model
{
    protected $table = 'team_members';
    protected $guarded = [];
    
    protected $casts = [
        'documents' => 'array',
        'join_date' => 'date',
    ];

    public function projects() 
    {
        return $this->belongsToMany(Project::class, 'project_team_member') ->withPivot(['role','created_at'])->withTimestamps();
    }
    use HasFactory;

    public function teamFormation()
    {
        return $this->belongsTo(TeamFormation::class, 'team_formation_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeFounders($q)
    {
        return $q->where('TeamMember', 'Founder');
    }
}
