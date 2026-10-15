<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team_member extends Model
{
    protected $table = 'team_members';
    // Company_id, user_id and team_formation_id are owner foreign keys: never mass-assignable, set them explicitly.
    protected $fillable = [
        'TeamMember',
        'Name',
        'Email',
        'LinkedIn',
        'RoleShereholder',
        'PresentagShereholder',
        'investorType',
        'acceptInvition',
        'isTeamLeader',
        'code',
        'expire_at',
        'region',
        'gender',
        'employment_type',
        'hiring_date',
        'documents',
        'phone',
        'join_date',
        'founder_type',
        'working_commitment',
        'national_id',
        'educational_status',
        'date_of_birth',
    ];
    
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
