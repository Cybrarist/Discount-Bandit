<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class GeneralHelper
{
    public static function get_numbers_only_with_dot($string): ?string
    {
        return preg_replace('/[^0-9.]/', '', $string);
    }

    public static function get_numbers_only($string): ?int
    {
        return (int) preg_replace('/[^0-9]/', '', $string);
    }

    public static function get_letters_only($string): ?string
    {
        return preg_replace('/[^a-zA-Z]/', '', $string);
    }

}
