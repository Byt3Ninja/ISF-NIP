<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasProtectedDocuments;
use App\Enums\ProtectedFileSet;

class Milestone extends Model
{
    use HasFactory, SoftDeletes, HasProtectedDocuments;
    
    // Company_id is the owner foreign key: never mass-assignable (the company relation sets it).
    protected $fillable = [
        'name',
        'description',
        'completion_percentage',
        'documents',
        'category',
        'status',
        'completed',
        'milestone_date',
        'visibility',
        'cohort',
        'attachment',
    ];
    
    protected $casts = [
        'documents' => 'array',
        'completion_percentage' => 'integer',
    ];

    /**
     * Supporting documents as [{name, path, url}], with `url` recomputed from each
     * file's record (authorized route, or null for a viewer who may not open it).
     * Also unwraps the double-encoded JSON older rows contain.
     */
    public function getDocumentsAttribute($value)
    {
        return $this->protectedDocumentList($value, ProtectedFileSet::MILESTONE_DOCUMENT, 'milestone');
    }

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
