<?php

namespace App\Filament\Resources\GroupListResource\Pages;

use App\Filament\Resources\GroupListResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGroupLists extends ListRecords
{
    protected static string $resource = GroupListResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
