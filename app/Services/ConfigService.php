<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Configuration;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    /**
     * Get a configuration value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::rememberForever("config_{$key}", function () use ($key) {
            $config = Configuration::query()->where('key', $key)->first();

            return $config ? $config->value : null;
        });

        return $value ?? $default;
    }

    /**
     * Set a configuration value.
     */
    public function set(string $key, mixed $value): Configuration
    {
        $config = Configuration::query()->where('key', $key)->firstOrFail();

        $config->update(['value' => $value]);

        return $config;
    }
}
