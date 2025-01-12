<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum TaskListTab: string implements HasIcon, HasLabel
{
    case DUE = 'Due';
    case ONGOING = 'Ongoing';
    case COMPLETED = 'Completed';

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DUE => 'heroicon-o-exclamation-triangle',
            self::ONGOING => 'heroicon-o-arrow-trending-up',
            self::COMPLETED => 'heroicon-o-check-badge',
        };
    }

    public function getLabel(): string
    {
        return Str::title($this->value);
    }
}
