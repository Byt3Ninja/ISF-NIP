<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'is_other'];
    protected $table = 'static_categories';


    public function sectors()
    {
        return $this->belongsToMany(Sector::class, 'industry_sector', 'industry_id', 'sector_id');
    }
}
