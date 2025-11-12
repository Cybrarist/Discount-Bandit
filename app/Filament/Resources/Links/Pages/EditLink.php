<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Resources\Links\LinkResource;
use App\Helpers\ProductHelper;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditLink extends EditRecord
{
    protected static string $resource = LinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open')
                ->outlined()
                ->icon(Heroicon::Link)
                ->url(fn ($record) => ProductHelper::get_url($record), true),
        ];
    }
}
