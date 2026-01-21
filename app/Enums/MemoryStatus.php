<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MemoryStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Deprecated = 'deprecated';
    case Draft = 'draft';
    case Locked = 'locked';
    case Verified = 'verified';

    public function getColor(): array|string|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Verified => 'info',
            self::Locked => 'danger',
            self::Deprecated => 'warning',
            self::Active => 'success',
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
}
