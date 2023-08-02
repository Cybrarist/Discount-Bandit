<?php

namespace App\Filament\Widgets;

use App\Enums\StatusEnum;
use App\Models\PriceHistory;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PriceHistoryChart extends ApexChartWidget
{
    public ?Model $record = null;

    protected int | string | array $columnSpan = 'full';

    /**
     * Chart Id
     *
     * @var string
     */
        protected static string $chartId = 'priceHistoryChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Price History Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $product=$this->record;
        $services=$product->services()->pluck('name', 'services.id')->map(function ($record){
                return [
                        "name"=>$record,
                ];
        })->toArray();

        $available_services=implode("," ,  array_keys($services));
        $price_history=DB::select("
                                    SELECT service_id, GROUP_CONCAT(CONCAT(date, '_', price)) AS date_price
                                    FROM price_histories
                                    WHERE product_id =  $product->id
                                    and service_id IN ($available_services)
                                    GROUP BY service_id;
                                    ") ;
        foreach ($price_history as $single_price_history)
        {
            $dates_prices=explode(",", $single_price_history->date_price);
            foreach ($dates_prices as $date_price)
            {
                $seperated=explode("_", $date_price);
                $services[$single_price_history->service_id]["data"][]=[
                    'x'=>$seperated[0],
                    'y'=>((int)($seperated[1]))/100
                ];
            }
        }

        foreach ($services as $index=>$service)
        {
            if (sizeof($service) == 1)
                $services[$index]['data']=[];
        }


        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series'=>array_values($services),
            'colors' => ['#6366f1','#ffffff'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ]

        ];
    }


    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->default(now()->subMonth()),
            DatePicker::make('end_date')
                ->default(now()),
        ];
    }


}


