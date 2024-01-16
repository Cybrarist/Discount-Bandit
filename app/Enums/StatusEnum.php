<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Arr;

enum StatusEnum : string implements HasLabel
{
    case Published='p';
    case Disabled='d';
    case Silenced='s';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function to_array(): array
    {
        return array_combine(self::values(), self::names());
    }

    public static function ignored():array
    {
        return [
            self::Disabled,
        ];
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
