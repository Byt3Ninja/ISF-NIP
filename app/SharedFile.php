<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedFile extends Model
{
    use HasFactory;
     protected $fillable = [
        'team_formation_id',
        'team_member_id',
        'file',
        'original_name',
        'description',
        'user_id',
    ];

    // العلاقات
    public function teamFormation()
    {
        return $this->belongsTo(TeamFormation::class, 'team_formation_id');
    }

    public function teamMember()
    {
        return $this->belongsTo(Team_member::class, 'team_member_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
