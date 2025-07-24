<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
   protected $fillable = ['country_id','name_en','name_ar'];

    public function country(){
        return $this->belongsTo(Country::class,'country_id');
    }
    public function cities(){
        return $this->hasMany(City::class,'state_id');
    }
}
