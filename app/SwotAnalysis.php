<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwotAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'strength',
        'weakness',
        'opportunity',
        'threat',
        'srecommendation',
        'wrecommendation',
        'orecommendation',
        'trecommendation',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
