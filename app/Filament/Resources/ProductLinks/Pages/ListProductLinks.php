<?php

namespace App\Filament\Resources\ProductLinks\Pages;

use App\Filament\Resources\ProductLinks\ProductLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductLinks extends ListRecords
{
    protected static string $resource = ProductLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
