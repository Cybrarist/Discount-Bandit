<?php

namespace App\Services;

use App\Models\Link;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class PriceHistoryService
{
    public static function series_for_product(Product $product, CarbonInterface $start_date, CarbonInterface $end_date): array
    {
        return Link::whereHas('products', fn ($query) => $query->where('products.id', $product->id))
            ->with([
                'store',
                'store.currency',
                'link_histories' => fn ($query) => $query
                    ->orderBy('date', 'desc')
                    ->whereBetween('date', [$start_date, $end_date]),
            ])
            ->get()
            ->map(fn (Link $link): array => [
                'name' => Str::words($link->name, 4)." ({$link->store->currency->currency_symbol})",
                'data' => $link->link_histories
                    ->map(fn ($link_history): array => [
                        'x' => $link_history->date->toDateString(),
                        'y' => $link_history->price,
                    ])
                    ->all(),
            ])
            ->all();
    }

    /**
     * The link's price, plus its used price when the store reports one.
     *
     * @return array<int, array{name: string, data: array<int, array{x: string, y: float}>}>
     */
    public static function series_for_link(Link $link, CarbonInterface $start_date, CarbonInterface $end_date): array
    {
        $link->loadMissing('store.currency');

        $link_histories = $link->link_histories()
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date')
            ->get();

        $currency_symbol = $link->store->currency->currency_symbol;

        $series = [[
            'name' => "Price ({$currency_symbol})",
            'data' => $link_histories
                ->map(fn ($link_history): array => ['x' => $link_history->date->toDateString(), 'y' => $link_history->price])
                ->all(),
        ]];

        if ($link_histories->contains(fn ($link_history): bool => $link_history->used_price > 0)) {
            $series[] = [
                'name' => "Used Price ({$currency_symbol})",
                'data' => $link_histories
                    ->map(fn ($link_history): array => ['x' => $link_history->date->toDateString(), 'y' => $link_history->used_price])
                    ->all(),
            ];
        }

        return $series;
    }
}
