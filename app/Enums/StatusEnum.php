<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum StatusEnum : string
{
    case Deleted ='x';

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
            self::Deleted,
        ];
    }
}
