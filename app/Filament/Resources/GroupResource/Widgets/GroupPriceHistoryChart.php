<?php

namespace App\Filament\Resources\GroupResource\Widgets;

use App\Models\GroupPriceHistory;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GroupPriceHistoryChart extends ApexChartWidget
{
    public ?Model $record = null;

    protected static bool $deferLoading = true;

    /**
     * Chart Idcreate
     */
    protected static ?string $chartId = "groupPriceHistoryChart";

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
            $group_price_histories = GroupPriceHistory::whereDate('date', '>=', today()->subYear())
                ->where('group_id', $this->record->id)
                ->get(['price', 'date'])
                ->map(function ($item) {
                    return [
                        'x' => $item->date,
                        'y' => $item->price,
                    ];
                })->toArray();

            return [
                'chart' => [
                    'type' => 'area',
                    'height' => 300,
                ],
                'series' => [
                    [
                        'name' => 'Group History',
                        'data' => $group_price_histories,
                    ],
                ],
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
