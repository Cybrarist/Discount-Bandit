<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Services\PriceHistoryService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PriceHistoryChart extends ApexChartWidget
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
    protected static ?string $chartId = 'priceHistoryChart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $time_series = PriceHistoryService::series_for_product(
            $this->record,
            Carbon::parse($this->filters['start_date']),
            Carbon::parse($this->filters['end_date']),
        );

        return [
            'chart' => [
                'type'   => 'area',
                'height' => 300,
            ],
            'series' => $time_series,
            'xaxis'  => [
                "type"   => 'datetime',
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
                'shared'       => true,
                'intersect'    => false,
                'followCursor' => true,
                'x'            => [
                    'show'   => true,
                    'format' => 'yyyy-MM-dd',
                ],
                'marker' => [
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
