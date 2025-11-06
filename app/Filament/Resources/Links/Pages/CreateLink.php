<?php

namespace App\Filament\Resources\Links\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Links\LinkResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLink extends CreateRecord
{
    protected static string $resource = LinkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (
            Auth::user()->role == RoleEnum::User->value &&
            Auth::user()->other_settings['max_links'] &&
            Auth::user()->links()->count() > Auth::user()->other_settings['max_links']
        ) {
            Notification::make()
                ->title('You have reached the maximum number of links')
                ->body('Please delete some links before adding a new one, or speak to admin')
                ->danger()
                ->send();

            $this->halt();
        }

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
                'link_id' => $this->record->id,
            ]);

        \DB::table('link_product')->where([
            'link_id' => $this->record->id,
            'product_id' => $this->data['product_id'],
        ])->update([
            'user_id' => Auth::user()->id,
        ]);

    }
}
