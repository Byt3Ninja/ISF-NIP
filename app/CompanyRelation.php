<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyRelation extends Model
{
    use HasFactory;

    protected $table = 'company_relations';

    protected $fillable = [
        'company_id',
        'industry_id',
        'sector_id',
        'industry_sector_id',
        'technology_id',
        'user_id',
    ];

    // العلاقات

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function industry()
    {
        return $this->belongsTo(StaticCategory::class, 'industry_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function technology()
    {
        return $this->belongsTo(Technology::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
