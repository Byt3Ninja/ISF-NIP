<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingDuration extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'company_id', 'cycle_id', 'step_id', 'training_sessions_id', 'page_url', 'duration_minutes'];
}
