<?php

namespace App\Providers;

use App\Filament\Pages\Profile\EditProfile;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class McpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::auth.edit-profile.form.after',
            fn (): string => Blade::render('<div class="mt-8">@livewire(\'profile.manage-mcp-tokens\')</div>'),
            scopes: EditProfile::class,
        );
    }
}
