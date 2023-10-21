<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Classes\MainStore;
use App\Classes\Stores\Amazon;
use App\Classes\URLHelper;
use App\Filament\Resources\ProductResource;
use App\Models\Store;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
//    use HasWizard;

    protected static string $resource = ProductResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $url=new URLHelper($data['url']);

        if (!MainStore::validate_url($url))
            $this->halt();

        $url->fill_data($this->data);

        return \Arr::except($this->data , "url");
    }


    protected  function  afterCreate() : void
    {

        $url=new URLHelper($this->data['url']);

        $store=Store::where('domain' , $url->domain)->first();
        $store->products()->withPivot([
            //data
            'notify_price',
            //amazon
            //ebay
            'ebay_id' ,
            'remove_if_sold'

        ])->updateOrCreate(
            ['products.id'=>$this->record->id],
            [],
            ['product_store.ebay_id'=>$this->data['ebay_id'] ?? null,
            'product_store.notify_price'=>$this->data['notify_price'] * 100 ?? 0,
            'product_store.remove_if_sold'=>$this->data['remove_if_sold'] ?? false,
            ]
        );

        if ($this->data['variation_options']){
            if (MainStore::is_amazon($this->data['url'])){
                Amazon::insert_variation($this->data['variation_options'], $store, $this->data);
            }
        }
    }



}
