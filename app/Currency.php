<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $fillable = ['currency_code','currency_name','currency_symbol','exchange_rate_to_usd'];
}
