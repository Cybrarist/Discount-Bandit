<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Models\Link;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PriceHistoryLineChart extends ChartWidget
{
    use HasFiltersSchema;

    public ?Model $record = null;

    protected ?string $heading = 'Price History Line Chart';

    protected string $view = 'filament.widgets.price-history-chart';

    protected int|string|array $columnSpan = 'full';

    public string $minHeight = '50vh';

    protected ?string $maxHeight = '500px';

    protected ?array $options = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'tooltip' => [
                'mode' => 'index',
            ],
        ],
        'interaction' => [
            'mode' => 'nearest',
            'axis' => 'x',
            'intersect' => false,
        ],
        'scales' => [
            'x' => [
                'min' => '2025-01-01',
                'type' => 'time',
                'time' => [
                    'unit' => 'month',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Month',
                ],
            ],
            'y' => [
                'stacked' => false,
                'title' => [
                    'display' => true,
                    'text' => 'Price',
                ],
                'min' => 0,
            ],
        ],
    ];

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $time_series = [];
        $min_date = Carbon::parse('2025-01-01');
        $links = Link::whereHas('products', function ($query) {
            $query->where('products.id', $this->record->id);
        })
            ->with([
                'store',
                'store.currency',
                'link_histories' => function ($query) {
                    $query->orderBy('date', 'desc');
                },
            ])
            ->get()
            ->each(function (Link $link) use (&$time_series, &$min_date) {

                $name = Str::words($link->name, 4);
                $time_series[$link->id] = [
                    'name' => "{$name} ({$link->store->currency->currency_symbol})",
                    'data' => [],
                ];

                $min_date_for_current_link = $link->link_histories?->last()?->date;
                if ($min_date_for_current_link?->gt($min_date)) {
                    $min_date = $min_date_for_current_link;
                }

                foreach ($link->link_histories as $link_history) {
                    $time_series[$link->id]['data'][] = [
                        'x' => $link_history->date->toDateString(),
                        'y' => $link_history->price,
                    ];
                }

            });

        $finalDataSet = [];
        $this->options['scales']['x']['min'] = $min_date->toDateString();


        foreach ($time_series as $time_series_data) {
            $finalDataSet[] = [
                'label' => $time_series_data['name'],
                'data' => $time_series_data['data'],
                'borderColor' => 'rgb(255, 99, 132)',
                'backgroundColor' => 'rgb(255, 99, 132)',
                'fill' => true,
            ];
        }

        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => $finalDataSet,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate')
                ->default(now()->subDays(30)),
            DatePicker::make('endDate')
                ->default(now()),
        ]);
    }
}
