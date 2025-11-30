<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GetChartForCurrentLinkAndItsRelatedLinksForTheUserProducts extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Link $link)
    {
        $product_have_the_link = DB::table('link_product')
            ->where('link_id', $link->id)
            ->pluck('product_id');

        $links = Product::withoutGlobalScopes()
            ->where('products.user_id', Auth::id())
            ->whereIn('products.id', $product_have_the_link)
            ->join('link_product', 'products.id', '=', 'link_product.product_id')
            ->pluck('link_product.link_id');

        $start_date = today()->subYear();
        $end_date = today();
        $time_series = [];



        Link::whereHas('products', function ($query) use ($product_have_the_link) {
            $query->whereIn('products.id', $product_have_the_link);
        })
            ->with([
                'store',
                'store.currency',
                'link_histories' => function ($query) use ($start_date, $end_date) {
                    $query->orderBy('date', 'desc')
                        ->whereBetween('date', [$start_date, $end_date]);
                },
            ])
            ->get()
            ->each(function (Link $link) use (&$time_series) {

                $name = Str::words($link->name, 4);
                $time_series[$link->id] = [
                    'name' => "{$name} ({$link->store->currency->currency_symbol})",
                    'data' => [],
                ];

                foreach ($link->link_histories as $link_history) {
                    $time_series[$link->id]['data'][] = [
                        'x' => $link_history->date->toDateString(),
                        'y' => $link_history->price,
                    ];
                }

            });

        return array_values($time_series);
        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series' => array_values($time_series),
            'xaxis' => [
                "type" => 'datetime',
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                "decimalsInFloat" => 2,
            ],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
                'followCursor' => true,
                'x' => [
                    'show' => true,
                    'format' => 'yyyy-MM-dd',
                ],
                'marker' => [
                    'show' => true,
                ],
            ],
        ];



    }
}
