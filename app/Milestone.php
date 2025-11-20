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
}
