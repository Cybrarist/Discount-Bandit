<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Helpers\StoreHelper;
use App\Helpers\StoresAvailable\StoreTemplate;
use App\Helpers\URLHelper;
use App\Models\ProductStore;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{

    protected static string $resource = ProductResource::class;


    private URLHelper $product_url;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->product_url=new URLHelper($data['url']);

        if(!StoreHelper::is_unique($this->product_url))
            $this->halt();

        return $data;
    }

    protected  function  afterCreate() : void
    {

        $product_store=ProductStore::updateOrCreate([
            "store_id" => $this->product_url->store->id,
            "product_id" => $this->record->id,
            "key"=>$this->product_url->product_unique_key
        ],[
            "notify_price" => $this->data['notify_price'] ?? 0,
        ]);

        $product_store->load([
            "store"
        ]);

        if (!empty($this->data['variation_options'])){
            StoreTemplate::insert_variation( $this->data["variation_options"] , $product_store->store , $this->data);
        }

    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
}


}
