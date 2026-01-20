<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MemoryType: string implements HasColor, HasIcon, HasLabel
{
    case BusinessRule = 'business_rule';
    case DecisionLog = 'decision_log';
    case Preference = 'preference';
    case SystemConstraint = 'system_constraint';
    case Documentation = 'documentation';
    case TechStack = 'tech_stack';
    case Fact = 'fact';
    case Task = 'task';
    case Architecture = 'architecture';
    case UserContext = 'user_context';
    case Convention = 'convention';
    case Risk = 'risk';

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
            self::Risk => 'Risk',
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
            self::Risk => 'heroicon-o-exclamation-triangle',
        };
    }

    public function getColor(): string|array|null
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
            self::Risk => 'rose',
        };
    }
}
