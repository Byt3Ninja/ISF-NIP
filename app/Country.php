<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name_en','name_ar','iso3','phone_code'];
    public function states(){
        return $this->hasMany(State::class,'country_id');
    }
}
