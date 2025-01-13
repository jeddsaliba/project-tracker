<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum UserListTab: string implements HasIcon, HasLabel
{
    case PROJECTS = 'Projects';
    case TASKS = 'Tasks';

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PROJECTS => 'heroicon-o-exclamation-triangle',
            self::TASKS => 'heroicon-o-arrow-trending-up',
        };
    }

    public function getLabel(): string
    {
        return Str::title($this->value);
    }
}
