<?php

namespace App\Services;

use App\Models\Configuration;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    /**
     * Get a configuration value by key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::rememberForever("config_{$key}", function () use ($key) {
            $config = Configuration::where('key', $key)->first();

            return $config ? $config->value : null;
        });

        return $value ?? $default;
    }

    /**
     * Set a configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return Configuration
     */
    public function set(string $key, mixed $value): Configuration
    {
        $config = Configuration::where('key', $key)->firstOrFail();

        $config->update(['value' => $value]);

        return $config;
    }
}
