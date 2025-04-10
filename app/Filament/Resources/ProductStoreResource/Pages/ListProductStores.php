<?php

namespace App\Filament\Resources\ProductStoreResource\Pages;

use App\Filament\Resources\ProductStoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductStores extends ListRecords
{
    protected static string $resource = ProductStoreResource::class;

    public function setPage($page, $pageName = 'page'): void
    {
        parent::setPage($page, $pageName);

        $this->dispatch('scroll-to-top');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
