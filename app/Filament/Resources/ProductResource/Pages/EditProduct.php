<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Widgets\PriceHistoryChart;
use App\Helpers\StoreHelper;
use App\Helpers\StoresAvailable\StoreTemplate;
use App\Helpers\URLHelper;
use App\Models\ProductStore;
use App\Models\Store;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = "Edit Product";

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('Fetch')
                ->color('primary')
                ->action(fn () => StoreHelper::fetch_product($this->record))
                ->after(fn ($livewire) => $livewire->dispatch('refresh')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $extra_data = [
            'notify_price' => $data['notify_price'] ?? null,
            'notify_percentage' => $data['notify_percentage'] ?? null,
        ];

        if ($data['url']) {
            $new_url = new URLHelper($data['url']);

            $extra_data['key'] = $new_url->product_unique_key;

            // if product across multi region store has the same id, then user can just add the domain
            // and the code will add the other store.

            if (! $new_url->product_unique_key) {
                $get_same_store_product = ProductStore::where("product_id", $this->record->id)
                    ->whereIn("store_id", Store::where('domain', 'like', $new_url->top_host.'%')->pluck("id")->toArray())
                    ->first();

                $extra_data['key'] = $get_same_store_product->key;
            } elseif (! StoreHelper::is_unique($new_url)) {
                $this->halt();
            }

            StoreTemplate::insert_other_store(domain: $new_url->domain, product_id: $this->record->id, extra_data: $extra_data);

            $this->dispatch('refresh_products_relation');
        }

        return Arr::except($data, ["url"]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            PriceHistoryChart::class,
        ];
    }

    #[On('refresh')]
    public function refresh_the_form(): void
    {
        $this->refreshFormData(
            $this->record->getFillable()
        );
    }
}
