<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Classes\MainStore;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\Ebay;
use App\Classes\URLHelper;
use App\Filament\Resources\ProductResource;
use App\Filament\Widgets\PriceHitoryChart;
use App\Models\Product;
use App\Models\Store;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;


//    protected function getRedirectUrl(): ?string
//    {
//        return route('filament.admin.resources.products.edit', $this->record->id);
//    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('Fetch')->color('primary')->action(function ($record){
                try {

                    $product_stores=DB::table('product_store')
                        ->where('product_id', $this->record->id)
                        ->join('stores', 'store_id', '=' , 'stores.id')
                        ->orderBy('product_store.updated_at')
                        ->get();


                    foreach ($product_stores as $product_store)
                        if (MainStore::is_amazon($product_store->slug))
                            new Amazon(Product::find($product_store->product_id), Store::find($product_store->store_id));
                        else if (is_ebay($product_store->slug))
                            new Ebay(Product::find($product_store->product_id), Store::find($product_store->store_id) , null, $product_store->ebay_id);

                    Notification::make()
                        ->title('Data Has been Fetched, Please refresh to see the new values.')
                        ->success()
                        ->send();
                }
                catch ( \Exception $e){
                    Log::error("Couldn't fetch the job with error : $e" );
                    Notification::make()
                        ->title("Couldn't fetch the product, refer to logs")
                        ->danger()
                        ->send();
                }

            })

        ];
    }


    protected function mutateFormDataBeforeSave(array $data) : array{

        $extra_keys=[];
        $extra_data=['notify_price'=>$data['notify_price'] ?? null,];
        if ($data['url']){
            $url=new URLHelper($data['url']);
            if (MainStore::is_ebay($url->domain)){
                $extra_keys=['ebay_id'=>$url->get_ebay_item_id()];
                $extra_data=\Arr::add($extra_data , 'remove_if_sold' , $data['remove_if_sold']  ?? null);
            }

            MainStore::insert_other_store(domain: $url->domain , product_id: $this->record->id, extra_keys: $extra_keys, extra_data: $extra_data);
            $this->dispatch('refresh_products_relation');
        }
        return \Arr::except($data , ["url"]);
    }

    protected function getFooterWidgets(): array
    {
        if ($this->getRecord()->stores()->count() )
            return [
                PriceHitoryChart::class
            ];
        return [];

    }
}
