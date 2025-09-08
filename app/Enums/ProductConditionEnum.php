<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductConditionEnum: string implements HasLabel, HasColor
{
    case New = 'new';
    case Used = 'used';
    case Refurbished = 'refurbished';
    case Other = 'other';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::New => 'success',
            self::Used => 'warning',
            self::Refurbished => 'info',
            self::Other => 'gray',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }
}
