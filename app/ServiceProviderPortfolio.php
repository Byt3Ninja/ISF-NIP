<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = ['media_full_url'];

    public function getMediaFullUrlAttribute()
    {
        $value = $this->attributes['media_url'] ?? null;
        if (!$value) return null;
        if (str_starts_with($value, 'http')) return $value;

        // New files in Laravel storage
        if (Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        // Legacy files in public/191014/
        return '/191014/' . ltrim($value, '/');
    }

    public function serviceProvider()
    {
        return $this->belongsTo(serviceProviders::class, 'service_provider_id');
    }
}
