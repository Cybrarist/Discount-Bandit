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

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
