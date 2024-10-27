<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class GeneralHelper
{

    public static function get_numbers_only_with_dot($string): array|string|null
    {
        return preg_replace('/[^0-9.]/', '', $string);
    }


    public static function get_value_from_meta_tag(array $meta_items, string $key , string $attribute): string
    {
        foreach ($meta_items as $meta)
            foreach ($meta->attributes() as  $value)
                if ($value == $key)
                    return $meta->attributes()[$attribute]->__toString();

        return "";
    }

}
