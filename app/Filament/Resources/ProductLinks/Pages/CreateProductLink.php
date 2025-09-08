<?php

namespace App\Filament\Resources\ProductLinks\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\ProductLinks\ProductLinkResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductLink extends CreateRecord
{
    protected static string $resource = ProductLinkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (
            Auth::user()->role == RoleEnum::User->value &&
            Auth::user()->other_settings['max_links'] &&
            Auth::user()->product_links()->count() > Auth::user()->other_settings['max_links']
        ) {
            Notification::make()
                ->title('You have reached the maximum number of links')
                ->body('Please delete some links before adding a new one, or speak to admin')
                ->danger()
                ->send();

            $this->halt();
        }

        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->notification_settings()
            ->create([
                'price_desired' => $this->data['price_desired'],
                'percentage_drop' => $this->data['percentage_drop'],
                'price_lowest_in_x_days' => $this->data['price_lowest_in_x_days'],
                'is_in_stock' => $this->data['is_in_stock'],
                'is_shipping_included' => $this->data['is_shipping_included'],
                'any_price_change' => $this->data['any_price_change'],
                'is_official' => $this->data['is_official'],
                'product_link_id' => $this->record->id,
                'user_id' => Auth::id(),
            ]);
    }
}
