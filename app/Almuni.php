<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almuni extends Model
{
    use HasFactory;

    protected $table = 'alumni';

    protected $fillable = [
        'user_id',
        'cycle_id',
        'basic_startup_information',
        'startup_leader_information',
        'founders',
        'employees',
        'shareholders',
        'investors',
        'sustainability_goals',
        'pitch_deck',
        'legal_information',
        'program',
        'screening',
        'final_screening',
        'training',
        'mentorship',
        'milestones',
        'tasks',
        'quizzes',
        'surveys',
        'services',
        'submitted_forms',
        'other',
    ];

    protected $casts = [
        'basic_startup_information' => 'array',
        'startup_leader_information' => 'array',
        'founders' => 'array',
        'employees' => 'array',
        'shareholders' => 'array',
        'investors' => 'array',
        'sustainability_goals' => 'array',
        'pitch_deck' => 'array',
        'legal_information' => 'array',
        'program' => 'array',
        'screening' => 'array',
        'final_screening' => 'array',
        'training' => 'array',
        'mentorship' => 'array',
        'milestones' => 'array',
        'tasks' => 'array',
        'quizzes' => 'array',
        'surveys' => 'array',
        'services' => 'array',
        'submitted_forms' => 'array',
        'other' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function getScreeningDataAttribute()
    {
        return collect(  $this->screening );
    }

    public function getFinalScreeningDataAttribute()
    {
        return collect(  $this->final_screening  );
    }
    
}
