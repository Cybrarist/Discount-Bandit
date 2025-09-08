<?php

namespace App\Filament\Resources\ProductLinks\Pages;

use App\Filament\Resources\ProductLinks\ProductLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductLink extends EditRecord
{
    protected static string $resource = ProductLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
