<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\DashboardPanelProvider;
use App\Providers\McpServiceProvider;
use App\Providers\VoltServiceProvider;

return [
    AppServiceProvider::class,
    DashboardPanelProvider::class,
    McpServiceProvider::class,
    VoltServiceProvider::class,
];
