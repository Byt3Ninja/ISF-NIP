<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

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
