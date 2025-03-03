<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Helpers\ProductHelper;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PriceHistoryChart extends ApexChartWidget
{
    public ?Model $record = null;

    protected static bool $deferLoading = true;

    /**
     * Chart Idcreate
     */
    protected static ?string $chartId = "priceHistoryChart";

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Price History';

    protected int|string|array $columnSpan = 'full';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected static ?string $pollingInterval = '300s';

    protected function getOptions(): array
    {
        try {

            $price_histories_per_store = ProductHelper::get_product_history_per_store($this->record->id);

            return [
                'chart' => [
                    'type' => 'area',
                    'height' => 300,
                ],
                'series' => array_values($price_histories_per_store),
                'theme' => [
                    "palette" => 'palette1',
                ],
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
            ];

        } catch (\Exception $e) {
            return [];
        }

    }

    protected function extraJsOptions(): ?\Filament\Support\RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: {
                labels: {
                    formatter: function (val, index) {
                        return val.toLocaleString('en-US');
                    }
                }
            }
        }
        JS);
    }
}
