<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasProtectedDocuments;
use App\Enums\ProtectedFileSet;

class Employee extends Model
{
    use SoftDeletes, HasProtectedDocuments;

    protected $fillable = [
        'company_id',
        'full_name',
        'job_title',
        'gender',
        'employment_type',
        'hiring_date',
        'documents'
    ];

    protected $casts = [
        'hiring_date' => 'date',
        'documents' => 'array'
    ];

    /**
     * Documents as [{name, path, url}], with `url` recomputed from each file's record
     * (authorized route, or null for a viewer who may not open it).
     */
    public function getDocumentsAttribute($value)
    {
        return $this->protectedDocumentList($value, ProtectedFileSet::EMPLOYEE_DOCUMENT, 'employee');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Add this to Company model
    // public function employees()
    // {
    //     return $this->hasMany(Employee::class);
    // }
}
