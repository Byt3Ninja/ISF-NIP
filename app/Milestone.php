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
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', '!=', 'private');
    }

    /**
     * Get category name by ID
     */
    public function getCategoryNameAttribute()
    {
        $categories = [
            1 => 'Finance',
            2 => 'Planning', 
            3 => 'Design',
            4 => 'Development',
            5 => 'Testing',
            6 => 'Delivery'
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
