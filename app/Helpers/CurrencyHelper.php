<?php

namespace App\Helpers;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyHelper
{
    public static function get_currencies($currency_id = null)
    {

        $currencies = Cache::remember('currencies', 86400, function () {
            return Currency::pluck("code", "id")->toArray();
        });

        if ($currency_id) {
            return $currencies[$currency_id];
        }

        return $currencies;
    }
}
