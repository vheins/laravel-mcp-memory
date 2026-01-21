<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configurations\Pages;

use App\Filament\Resources\Configurations\ConfigurationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConfiguration extends CreateRecord
{
    protected static string $resource = ConfigurationResource::class;
}
