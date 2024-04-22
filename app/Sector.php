<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'parent_id',
    ];
    public function getNameAttribute($key){
        return $this->attributes['name_'.app()->getLocale()];
    }

    public function children(){
        return $this->hasMany(Sector::class,'parent_id');
    }
}
