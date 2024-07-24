<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Enums\StatusEnum;
use App\Helpers\CurrencyHelper;
use App\Helpers\ProductHelper;
use App\Helpers\StoreHelper;
use App\Models\PriceHistory;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PriceHistoryChart extends ApexChartWidget
{
    public ?Model $record = null;

    protected static bool $deferLoading = true;

    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = "priceHistoryChart";

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

    protected static ?string $pollingInterval='300s';

    protected function getOptions(): array
    {
        try {


            $price_histories_per_store=ProductHelper::get_product_history_per_store($this->record->id);

            return [
                'chart' => [
                    'type' => 'area',
                    'height' => 300,
                ],
                'series'=>array_values($price_histories_per_store),
                'theme'=>[
                    "palette"=> 'palette1'
                ],
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
                ],



            ];

        }catch (\Exception $e){
            dd($e);
            return [];
        }

    }

}
