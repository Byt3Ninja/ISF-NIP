<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = ['name_en', 'name_ar', 'iso3', 'phone_code'];

    public function states()
    {
        return $this->hasMany(State::class, 'country_id');
    }
}
