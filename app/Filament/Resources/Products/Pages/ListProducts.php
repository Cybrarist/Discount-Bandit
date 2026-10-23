<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Services\PriceHistoryService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Renderless;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

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

    public function toggle_favourite(string $productKey): void
    {
        $product = Product::find($productKey);

        abort_unless($product, 404);

        $product->update(['is_favourite' => ! $product->is_favourite]);
    }

    #[Renderless]
    public function price_history_series(int $productId): array
    {
        $product = Product::findOrFail($productId);

        return PriceHistoryService::series_for_product($product, today()->subMonths(6), today());
    }

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

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
