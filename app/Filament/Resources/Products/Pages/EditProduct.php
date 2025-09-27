<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Http\Controllers\Actions\FetchAllLinksForProductAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon(Heroicon::Trash),

            Action::make('Fetch')
                ->color('primary')
                ->action(fn () => new FetchAllLinksForProductAction()->__invoke($this->record))
                ->after(fn ($livewire) => $livewire->dispatch('refresh')),
        ];
    }
}
