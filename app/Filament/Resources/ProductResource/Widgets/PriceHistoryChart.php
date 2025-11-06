<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Link;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PriceHistoryChart extends ApexChartWidget
{
    use HasFiltersSchema;

    public ?Model $record = null;

    protected static bool $deferLoading = true;

    protected static ?string $heading = 'Price History';

    protected int|string|array $columnSpan = 'full';

    /**
     * Chart Id
     */
    protected static ?string $chartId = 'priceHistoryChart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $start_date = $this->filters['start_date'];
        $end_date = $this->filters['end_date'];
        $time_series = [];

        Link::whereHas('products', function ($query) {
            $query->where('products.id', $this->record->id);
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

    protected function extraJsOptions(): ?\Filament\Support\RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: {
                labels: {
                    formatter: (val, index)  => {
                        return val.toLocaleString('en-US');
                    }
                }
            }
        }
        JS);
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('start_date')
                ->default(today()->subMonths(6)),

            DatePicker::make('end_date')
                ->default(today()),
        ]);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }
}
