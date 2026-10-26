<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Resources\Links\LinkResource;
use App\Models\Link;
use App\Services\PriceHistoryService;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Renderless;

class ListLinks extends ListRecords
{
    use HasResizableColumn;

    protected static string $resource = LinkResource::class;

    public const array DEFAULT_CONTENT_GRID = [
        'md'  => 2,
        'xl'  => 3,
        '2xl' => 4,
    ];

    public ?int $grid_columns = null;

    public function setPage($page, $pageName = 'page'): void
    {
        parent::setPage($page, $pageName);

        $this->dispatch('scroll-to-top');
    }

    #[Renderless]
    public function price_history_series(int $linkId): array
    {
        $link = Link::findOrFail($linkId);

        return PriceHistoryService::series_for_link($link, today()->subMonths(6), today());
    }

    /**
     * @return array<string, int>
     */
    public function content_grid(): array
    {
        if (blank($this->grid_columns)) {
            return self::DEFAULT_CONTENT_GRID;
        }

        $columns = max(2, min(6, $this->grid_columns));

        return [
            'md' => min($columns, 2),
            'lg' => min($columns, 3),
            'xl' => $columns,
        ];
    }
}
