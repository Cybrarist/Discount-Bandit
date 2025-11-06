<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductStatusEnum: string implements HasLabel, HasColor
{
    case Active = 'active';
    case Disabled = 'disabled';
    case Silenced = 'silenced';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Disabled => 'gray',
            self::Silenced => 'warning',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }
}
