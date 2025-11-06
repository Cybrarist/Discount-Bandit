<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StoreStatusEnum: string implements HasLabel
{
    case Active = 'active';
    case Disabled = 'disabled';
    case Silenced = 'silenced';

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }
}
