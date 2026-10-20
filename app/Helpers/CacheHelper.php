<?php

namespace App\Helpers;

use App\Models\Link;
use App\Models\Store;
use App\Services\URLParserService;
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

    /**
     * Short URL hosts of all stores, cleared when a store's short URL hosts change.
     *
     * @return list<string>
     */
    public static function get_short_url_hosts(): array
    {
        return Cache::remember('short_url_hosts', now()->addDay(), fn (): array => Store::whereNotNull('short_url_hosts')
            ->pluck('short_url_hosts')
            ->flatten()
            ->map(fn (?string $host): string => URLParserService::normalize_host($host))
            ->filter()
            ->unique()
            ->values()
            ->all());
    }

    public static function forget_short_url_hosts(): void
    {
        Cache::forget('short_url_hosts');
    }
}
