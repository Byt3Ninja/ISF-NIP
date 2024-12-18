<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name_ar', 'name_en', 'category_id', 'description','Type'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
