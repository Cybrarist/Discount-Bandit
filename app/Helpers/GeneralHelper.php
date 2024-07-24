<?php

namespace App\Helpers;

class GeneralHelper
{

    public static function get_numbers_only_with_dot($string): array|string|null
    {
        return preg_replace('/[^0-9.]/', '', $string);
    }


}
