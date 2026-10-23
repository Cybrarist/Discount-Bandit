<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

enum RoleEnum: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case User = 'user';

    public function getColor(): string|array|null
    {
        return match ($this) {
            RoleEnum::Admin => 'primary',
            RoleEnum::User => 'secondary',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return Str::of($this->name);
    }
}
