<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['category_name_ar','category_name_en','weight','type'];

    public function getNameAttribute()
    {
        return $this->{'category_name_' . app()->getLocale()};
    }

    public function subcategory()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }
    public function question()
    {
        return $this->hasMany(UserScore::class, 'category_id');
    }


}
