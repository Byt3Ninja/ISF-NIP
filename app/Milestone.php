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

    public function scopePublic($query)
    {
        return $query->where(function ($q) {
            $q->where('visibility', '!=', 'private')
              ->orWhereNull('visibility');
        });
    }

    /**
     * Get category name by ID
     */
    public function getCategoryNameAttribute()
    {
        $l = app()->getLocale();
        $categories = [
            1 => $l == 'en' ? 'Finance' : 'تمويل',
            2 => $l == 'en' ? 'Planning' : 'تخطيط', 
            3 => $l == 'en' ? 'Design' : 'تصميم',
            4 => $l == 'en' ? 'Development' : 'تطوير',
            5 => $l == 'en' ? 'Testing' : 'اختبار',
            6 => $l == 'en' ? 'Delivery' : 'تسليم'
        ];
        
        return $categories[$this->category] ?? $this->category;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'not_started' => __('startup.not_started'),
            'in_progress' => __('startup.in_progress'),
            'completed' => __('startup.completed'),
            'on_hold' => __('startup.on_hold'),
            'Not Started' => __('startup.not_started'),
            'In Progress' => __('startup.in_progress'),
            'Completed' => __('startup.completed'),
            'On Hold' => __('startup.on_hold'),
        ];
        
        return $statusLabels[$this->status] ?? $this->status;
    }
}
