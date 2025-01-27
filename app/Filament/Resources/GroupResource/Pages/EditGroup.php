<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Models\Product;
use App\Models\ProductStore;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {

        $products_to_add_to_group = [];

        // products that are already available in the system.
        foreach ($this->data['products_available'] as $available_products) {
            foreach ($available_products['product_id'] as $available_product) {
                $products_to_add_to_group[$available_products['key']][] = (int) $available_product;
            }
        }

        // new products

        // check if all stores links have the same currency as the current group
        // otherwise stop the process

        $count = 1;
        $products_to_add_to_app = [];
        foreach ($this->data['url_products'] as $new_product_url) {
            $url = new \App\Helpers\URLHelper($new_product_url['url']);
            // make sure the store url has the same currency as the group.
            if ($url->store->currency_id != $this->data['currency_id']) {
                Notification::make()
                    ->title('store with different currency is not allowed')
                    ->body("please remove product ({$count}) with link {$new_product_url['url']}")
                    ->danger()
                    ->send();
                $this->halt();
            }
            $count++;

            $products_to_add_to_app[] = [
                'key' => $new_product_url['key'],
                'product_key' => $url->product_unique_key,
                'store_id' => $url->store->id,
            ];
        }

        // check if that product is already available in database or not.
        // if it is, then add it to the list
        // if not, we create it and add it to the list

        foreach ($products_to_add_to_app as $product_app) {

            $product_store = ProductStore::where([
                'store_id' => $product_app['store_id'],
                'key' => $product_app['product_key'],
            ])->first();

            if (! $product_store) {
                $product = Product::create();

                $product->product_stores()->create([
                    "key" => $product_app['product_key'],
                    "store_id" => $product_app['store_id'],
                ]);

            }

            $products_to_add_to_group[$product_app['key']][] = (int) ($product_store?->product_id ?? $product->id);

        }

        foreach ($products_to_add_to_group as $key => $products) {
            // get unique values of the available products per key
            $unique_products = array_unique($products);

            foreach ($unique_products as $unique_product) {
                DB::table('group_product')
                    ->updateOrInsert([
                        'group_id' => $this->record->id,
                        'product_id' => $unique_product,
                    ], [
                        'key' => $key,
                    ]);
            }
        }
    }
}
