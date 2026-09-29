<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceProviderClient extends Model
{
    protected $table = 'service_provider_clients';

    protected $fillable = [
        'service_provider_id',
        'name',
        'logo_url',
        'external_link',
        'order',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(serviceProviders::class, 'service_provider_id');
    }
}
