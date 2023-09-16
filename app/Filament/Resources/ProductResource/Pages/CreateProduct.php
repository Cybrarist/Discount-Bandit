<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
//    use HasWizard;

    protected static string $resource = ProductResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $parsed_url=parse_url($data['url']);
        $parsed_url['host']=\Str::replace("www.", "" ,  $parsed_url['host']);

        //Check if the store exists.
        try {
            $store=Store::where('host'  , $parsed_url['host'])->firstOrFail();
        }
        catch (\Exception){
            Notification::make()
                ->danger()
                ->title("Wrong Store")
                ->body("this store doesn't exist in our database, please check the url")
                ->persistent()
                ->send();
            $this->halt();
        }

        if (is_amazon( $store->host)
            && validate_amazon_product($data , $parsed_url)
            && amazon_asin_unique($data , $store))
                    return \Arr::except($data , "url");

        elseif ( is_ebay( $store->host)
            && validate_ebay_product($this->data , $parsed_url)
            && ebay_itm_unique($this->data)){
                return \Arr::except($data , "url");
        }
        else
            $this->halt();

    }


    protected  function  afterCreate() : void
    {

        try {
            $host=\Str::replace('www.', '' , parse_url($this->data['url'])['host']);
            $store=Store::where('host', \Str::lower($host))->firstOrFail();
        }
        catch (\Exception){
            Notification::make()
                ->danger()
                ->title("Wrong Store")
                ->body("this store doesn't exist in our database, please check the url")
                ->persistent()
                ->send();
            $this->halt();
        }

        $store->products()->withPivot(['ebay_id' , 'notify_price'])->updateOrCreate(
            ['products.id'=>$this->record->id],
            [],
            ['product_store.ebay_id'=>$this->data['ebay_id'] ?? null,
            'product_store.notify_price'=>$this->data['notify_price'] * 100 ?? 0,]
        );

        if ($this->data['variation_options'])
        {
            if (is_amazon( parse_url($this->data['url'])['host']) )
            {
                foreach ($this->data['variation_options'] as $single)
                {
                    $store=Store::where('host',$host)->first();
                    $store->products()->withPivot('notify_price')->updateOrCreate(
                        ['asin'=>$single],
                        [],
                        ['product_store.notify_price'=>$this->data['notify_price'] * 100 ]
                    );
                }

            }
        }
    }



}
