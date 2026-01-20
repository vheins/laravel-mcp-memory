<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MemoryScope: string implements HasColor, HasIcon, HasLabel
{
    case System = 'system';
    case Organization = 'organization';
    case Repository = 'repository';
    case User = 'user';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::System => 'System',
            self::Organization => 'Organization',
            self::Repository => 'Repository',
            self::User => 'User',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::System => 'heroicon-o-computer-desktop',
            self::Organization => 'heroicon-o-building-office',
            self::Repository => 'heroicon-o-code-bracket',
            self::User => 'heroicon-o-user',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::System => 'danger',
            self::Organization => 'warning',
            self::Repository => 'info',
            self::User => 'success',
        };
    }
}
