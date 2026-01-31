<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceProviderPortfolio extends Model
{
    protected $table = 'service_provider_portfolios';

    protected $fillable = [
        'service_provider_id',
        'media_url',
        'media_type',
        'title',
        'description',
        'order',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(serviceProviders::class, 'service_provider_id');
    }
}
