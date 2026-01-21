<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MemoryType: string implements HasColor, HasIcon, HasLabel
{
    case Architecture = 'architecture';
    case BusinessRule = 'business_rule';
    case Convention = 'convention';
    case DecisionLog = 'decision_log';
    case Documentation = 'documentation';
    case Fact = 'fact';
    case Overview = 'overview';
    case Preference = 'preference';
    case Risk = 'risk';
    case SystemConstraint = 'system_constraint';
    case Task = 'task';
    case TechStack = 'tech_stack';
    case UserContext = 'user_context';

    public function getColor(): array|string|null
    {
        return match ($this) {
            self::BusinessRule => 'primary',
            self::DecisionLog => 'secondary',
            self::Preference => 'gray',
            self::SystemConstraint => 'danger',
            self::Documentation => 'info',
            self::TechStack => 'success',
            self::Fact => 'info',
            self::Task => 'warning',
            self::Architecture => 'indigo',
            self::UserContext => 'fuchsia',
            self::Convention => 'teal',
            self::Overview => 'sky',
            self::Risk => 'rose',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BusinessRule => 'heroicon-o-scale',
            self::DecisionLog => 'heroicon-o-clipboard-document-check',
            self::Preference => 'heroicon-o-adjustments-horizontal',
            self::SystemConstraint => 'heroicon-o-shield-exclamation',
            self::Documentation => 'heroicon-o-book-open',
            self::TechStack => 'heroicon-o-cpu-chip',
            self::Fact => 'heroicon-o-information-circle',
            self::Task => 'heroicon-o-check-circle',
            self::Architecture => 'heroicon-o-map',
            self::UserContext => 'heroicon-o-user-circle',
            self::Convention => 'heroicon-o-document-duplicate',
            self::Overview => 'heroicon-o-presentation-chart-bar',
            self::Risk => 'heroicon-o-exclamation-triangle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BusinessRule => 'Business Rule',
            self::DecisionLog => 'Decision Log',
            self::Preference => 'Preference',
            self::SystemConstraint => 'System Constraint',
            self::Documentation => 'Documentation',
            self::TechStack => 'Tech Stack',
            self::Fact => 'Fact',
            self::Task => 'Task',
            self::Architecture => 'Architecture',
            self::UserContext => 'User Context',
            self::Convention => 'Convention',
            self::Overview => 'Overview',
            self::Risk => 'Risk',
        };
    }
}
