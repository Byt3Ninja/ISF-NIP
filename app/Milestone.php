<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = [
        'documents' => 'array',
        'completion_percentage' => 'integer',
    ];

    /**
     * Append cohort to JSON serialization
     */
    protected $appends = ['cohort'];

    /**
     * Get the cohort attribute (handles case-sensitivity)
     * This ensures 'cohort' works even though DB column is 'Cohort'
     */
    public function getCohortAttribute()
    {
        // Check both variations to handle any case
        return $this->attributes['cohort'] ?? $this->attributes['Cohort'] ?? null;
    }

    /**
     * Set the cohort attribute (handles case-sensitivity)
     * This ensures setting 'cohort' writes to DB column 'Cohort'
     */
    public function setCohortAttribute($value)
    {
        $this->attributes['Cohort'] = $value;
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
