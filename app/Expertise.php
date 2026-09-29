<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function children()
    {
        return $this->hasMany(Expertise::class, 'parent_id');
    }

    // Parent → العنصر الأب
    public function parent()
    {
        return $this->belongsTo(Expertise::class, 'parent_id');
    }

    // Scope لجلب جميع العناصر الأب فقط (parent_id = null)
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope لجلب جميع الـ children فقط (parent_id != null)
    public function scopeChildrenOnly($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function companies()
    {
        return $this->belongsToMany(
            \App\Company::class,
            'company_expertise',
            'expertise_id',
            'company_id'
        )->withTimestamps();
    }
}
