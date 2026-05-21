<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $fillable = ['currency_code','currency_name','currency_name_ar','currency_symbol','exchange_rate_to_usd','flag'];

    public function getNameAttribute()
    {
        $name = app()->getLocale() === 'ar' ? $this->currency_name_ar : $this->currency_name;

        return $name ?? $this->currency_name;
    }
}
