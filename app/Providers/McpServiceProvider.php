<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class McpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Use direct embedding in view instead of hooks for reliability
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
}
