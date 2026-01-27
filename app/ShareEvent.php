<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_formation_id',
        'cycle_id',
        'user_id',
        'platform',
        'ip_address',
        'user_agent',
    ];

    public function teamFormation()
    {
        return $this->belongsTo(TeamFormation::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
