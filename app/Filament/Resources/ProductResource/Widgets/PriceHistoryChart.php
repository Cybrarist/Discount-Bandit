<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PriceHistoryChart extends ApexChartWidget
{
    public ?Model $record = null;

    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = "priceHistoryChart";

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Price History';


    protected int | string | array $columnSpan = 'full';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        if ($this->record){
            $product=$this->record;

            $stores=$product->stores()->pluck('name', 'stores.id')->map(function ($record){
                return [
                    "name"=>$record,
                ];
            })->toArray();
            $available_stores=implode("," ,  array_keys($stores));

            $price_history=DB::select("
                                    SELECT store_id, GROUP_CONCAT(CONCAT(date, '_', price)) AS date_price
                                    FROM price_histories
                                    WHERE product_id =  $product->id
                                    and store_id IN ($available_stores)
                                    GROUP BY store_id;
                                    ") ;


            foreach ($price_history as $single_price_history)
            {
                $dates_prices=explode(",", $single_price_history->date_price);
                foreach ($dates_prices as $date_price)
                {
                    $seperated=explode("_", $date_price);
                    $stores[$single_price_history->store_id]["data"][]=[
                        'x'=>$seperated[0],
                        'y'=>((int)($seperated[1]))/100
                    ];
                }
            }

            foreach ($stores as $index=>$store)
            {
                if (sizeof($store) == 1)
                    $stores[$index]['data']=[];
            }


            return [
                'chart' => [
                    'type' => 'area',
                    'height' => 300,
                ],
                'series'=>array_values($stores),
                'colors' => ['#6366f1','#ffffff'],
                'xaxis' => [
                    "type"=> 'datetime',
                    'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
                'stroke' => [
                    'curve' => 'smooth',
                ],
                'dataLabels' => [
                    'enabled' => false,
                ]

            ];

        }

        return [];
    }

}
