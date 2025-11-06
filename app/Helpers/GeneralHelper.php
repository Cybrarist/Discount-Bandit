<?php

namespace App\Helpers;

use Illuminate\Support\Uri;

class GeneralHelper
{
    public static function get_numbers_only_with_dot($string): ?string
    {
        return preg_replace('/[^0-9.]/', '', $string);
    }

    public static function get_numbers_only_with_dot_and_comma($string): ?string
    {
        return preg_replace('/[^0-9.,]/', '', $string);
    }

    public static function get_numbers_with_normalized_format(string $number): int|string
    {
        if (blank($number)) {
            return 0;
        }

        if (str_contains($number, ',') && str_contains($number, '.')) {
            return (strrpos($number, '.') < strrpos($number, ','))
                ? str_replace([',', '.'], ['.', ''], $number)
                : str_replace(',', '', $number);
        }

        if (str_contains($number, ',')) {
            return (strlen(substr($number, strrpos($number, ',') + 1)) === 2)
                ? str_replace(',', '.', $number)
                : str_replace(',', '', $number);
        }

        return $number;
    }

    public static function get_numbers_only($string): ?int
    {
        return (int) preg_replace('/[^0-9]/', '', $string);
    }

    public static function get_letters_only($string): ?string
    {
        return preg_replace('/[^a-zA-Z]/', '', $string);
    }

    public static function append_domain_to_url_if_missing(string &$url, string $domain)
    {
        $parsed_url = Uri::of($url);

        if (! $parsed_url->host()) {
            $url = "https://".$domain.$url;
        }
    }
}
