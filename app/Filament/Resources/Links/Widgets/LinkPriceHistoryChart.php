<?php

namespace App\Filament\Resources\Links\Widgets;

use App\Models\LinkHistory;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class LinkPriceHistoryChart extends ApexChartWidget
{
    use HasFiltersSchema;

    public ?Model $record = null;

    protected static bool $deferLoading = true;

    protected static ?string $heading = 'History';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = "600s";

    /**
     * Chart Id
     */
    protected static ?string $chartId = 'linkPriceHistoryChart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $start_date = Carbon::parse($this->filters['start_date']);
        $end_date = Carbon::parse($this->filters['end_date']);

        $this->record->loadMissing('store.currency');

        $link_histories = $this->record->link_histories()
            ->whereBetween('date', [
                $start_date,
                $end_date
            ])
            ->orderBy('date')
            ->get();

        $currency_symbol = $this->record->store->currency->currency_symbol;

        $time_series = [
            [
                'name' => "Price ({$currency_symbol})",
                'data' => $link_histories->map(fn(LinkHistory $link_history,) => [
                    'x' => $link_history->date->toDateString(),
                    'y' => $link_history->price,
                ])
                    ->all(),
            ],
        ];

        // not all stores have used price, only show it when there is one.
        if ($link_histories->contains(fn(LinkHistory $link_history,) => $link_history->used_price > 0)) {
            $time_series[] = [
                'name' => "Used Price ({$currency_symbol})",
                'data' => $link_histories->map(fn(LinkHistory $link_history,) => [
                    'x' => $link_history->date->toDateString(),
                    'y' => $link_history->used_price,
                ])
                    ->all(),
            ];
        }

        return [
            'chart'      => [
                'type'   => 'area',
                'height' => 300,
            ],
            'series'     => $time_series,
            'xaxis'      => [
                "type"   => 'datetime',
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis'      => [
                "decimalsInFloat" => 2,
            ],
            'stroke'     => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip'    => [
                'shared'       => true,
                'intersect'    => false,
                'followCursor' => true,
                'x'            => [
                    'show'   => true,
                    'format' => 'yyyy-MM-dd',
                ],
                'marker'       => [
                    'show' => true,
                ],
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
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
        JS
        );
    }

    public function filtersSchema(Schema $schema,): Schema
    {
        return $schema->components([
            DatePicker::make('start_date')
                ->default(today()->subMonths(6)),

            DatePicker::make('end_date')
                ->default(today()),
        ]);
    }

    public function updatedInteractsWithSchemas(string $statePath,): void
    {
        $this->updateOptions();
    }
}
