<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Resources\Links\LinkResource;
use App\Filament\Resources\Links\Widgets\LinkPriceHistoryChart;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Actions\FetchSingleLinkAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditLink extends EditRecord
{
    protected static string $resource = LinkResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            LinkPriceHistoryChart::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open')
                ->outlined()
                ->icon(Heroicon::Link)
                ->url(fn($record,) => LinkHelper::get_url($record), true),

            Action::make('reset_link')
                ->icon(Heroicon::Backspace)
                ->tooltip('Reset name and image, and fill them again on next fetch')
                ->action(function ($record,) {
                    $record->update([
                        'name'  => null,
                        'image' => null,
                    ]);

                    Notification::make()
                        ->title('name and image reseted successfully')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),


            Action::make('Fetch')
                ->icon(Heroicon::ArrowPath)
                ->color('success')
                ->action(fn($record,) => new FetchSingleLinkAction()->__invoke($record)),

        ];
    }
}
