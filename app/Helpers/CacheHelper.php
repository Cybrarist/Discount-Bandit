<?php

namespace App\Helpers;

use App\Models\Link;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    public static function get_stores_with_active_products()
    {
        return Cache::remember('stores_with_active_products', now()->addDay(), function () {
            return Store::whereIn('id', Link::distinct()->get('store_id')->toArray())
                ->with('currency:id,code,symbol')
                ->get(["id", "name", "currency_id", "domain"]);
        });
    }

    public static function get_stores_with_same_currency(int $currency_id)
    {
        return Cache::remember("store_currency_{$currency_id}", now()->addDay(), function () use ($currency_id) {
            return Store::where("currency_id", $currency_id)->get(['id', 'name']);
        });
    }
}
