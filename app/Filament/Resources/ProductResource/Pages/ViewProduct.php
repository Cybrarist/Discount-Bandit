<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Widgets\PriceHistoryChart;
use App\Helpers\StoreHelper;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Fetch')->color('primary')
                ->action(fn () => StoreHelper::fetch_product($this->record)),

        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PriceHistoryChart::class,
        ];
    }
}
