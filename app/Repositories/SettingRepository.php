<?php

namespace App\Repositories;

use App\Settings;
use Illuminate\Support\Facades\Cache;



class SettingRepository
{

    protected $cacheTime = 3600; // 1 hour
    /**
     * Summary of get
     * @param mixed $key
     * @param mixed $default
     */
    public function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", $this->cacheTime, function () use ($key) {
            $setting = Settings::where('type', $key)->first();
            if ($setting) {
                return $setting->set1;
            }
            $singleton = Settings::first();
            if ($singleton && array_key_exists($key, $singleton->getAttributes())) {
                return $singleton->{$key};
            }
            return null;
        }) ?? $default;
    }
    /**
     * Summary of set
     * @param mixed $key
     * @param mixed $value
     * @return Settings|\Illuminate\Database\Eloquent\Model
     */
    public function set($key, $value)
    {
        $setting = Settings::updateOrCreate(
            ['type' => $key],
            ['set1' => $value]
        );

        Cache::forget("setting_{$key}");
        return $setting;
    }
    /**
     * Summary of all
     * @return \Illuminate\Support\Collection
     * This method retrieves all settings from the cache or database
     */
    public function all()
    {
        return Cache::remember("settings_all", $this->cacheTime, function () {
            return Settings::all()->keyBy('type');
        });
    }

}