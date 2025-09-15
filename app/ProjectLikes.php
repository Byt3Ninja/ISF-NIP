<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLikes extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $fillable = ['team_id', 'user_id'];
}
