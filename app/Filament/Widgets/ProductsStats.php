<?php

namespace App\Filament\Widgets;

use App\Enums\StoreStatusEnum;
use App\Models\Link;
use App\Models\Product;
use App\Models\Store;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ProductsStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = "600s";

    protected function getStats(): array
    {
        $total_products = Cache::flexible('total_products', [5, 10], function () {
            return Product::count();
        });

        $total_active_stores = Cache::flexible('total_active_stores', [5, 10], function () {
            return Store::where('status', StoreStatusEnum::Active)->count();
        });

        $total_links = Cache::flexible('total_links', [5, 10], function () {
            return Link::count();
        });

        //        $links_out_of_stock = Cache::flexible('links_out_of_stock', [5, 10], function () {
        //            return Link::where('is_in_stock', false)
        //                ->orWhere(function (Builder $query) {
        //                    $query->where('price', 0)
        //                        ->where('used_price', 0);
        //                })->count();
        //        });

        //        $results = DB::table('links')
        //            ->selectRaw('SUM(price) as total_price, SUM(used_price) as total_used_price, SUM(is_in_stock) as total_in_stock')
        //            ->groupBy('product_id')
        //            ->get();
        //
        //
        //        $products_where_all_links_are_out_of_stock = Cache::flexible('products_where_all_links_are_out_of_stock', [5, 10], function () {
        //            // get the total sum of price and used price and if in stock since boolean is 0
        //            // then add them all together, and if it's greater than 0 then there's at least one link that's in stock
        //
        //            $results = DB::table('links')
        //                ->selectRaw('SUM(price) as total_price, SUM(used_price) as total_used_price, SUM(is_in_stock) as total_in_stock')
        //                ->groupBy('product_id')
        //                ->get();
        //
        //            dd($results);;
        //        });

        return [
            Stat::make('Active Stores', $total_active_stores)
                ->icon(Heroicon::OutlinedShoppingBag)
                ->color('info'),

            Stat::make('Total Products', $total_products)
                ->color('success'),
            Stat::make('Total Link', $total_links),
        ];
    }
}
