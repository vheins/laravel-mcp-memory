<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MemoryStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Verified = 'verified';
    case Locked = 'locked';
    case Deprecated = 'deprecated';
    case Active = 'active';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Verified => 'Verified',
            self::Locked => 'Locked',
            self::Deprecated => 'Deprecated',
            self::Active => 'Active',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Verified => 'heroicon-o-check-badge',
            self::Locked => 'heroicon-o-lock-closed',
            self::Deprecated => 'heroicon-o-archive-box-x-mark',
            self::Active => 'heroicon-o-bolt',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Verified => 'info',
            self::Locked => 'danger',
            self::Deprecated => 'warning',
            self::Active => 'success',
        };
    }
}
