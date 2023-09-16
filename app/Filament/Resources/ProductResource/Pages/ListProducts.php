<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Store;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }



    public function getTabs() : array {
    //get the stores with products in

        $active_stores=\Cache::get('active_stores');
        if (!$active_stores)
        {
            $active_stores=\DB::table('product_store')->distinct()->pluck('store_id');
            \Cache::put('active_stores', $active_stores , 1000);
        }

        $tabs=[
            'all' => ListRecords\Tab::make(),
        ];

        $stores=\Cache::get('stores_available');
        if (!$stores)
        {
            $stores=Store::whereIn('id', $active_stores)->where('tabs', true)->get();
            \Cache::put('stores_available', $stores , 6000 );
        }
        foreach ($stores as $store)
        {
            $tabs =\Arr::add($tabs , $store->name,
                ListRecords\Tab::make()->modifyQueryUsing(function (Builder $query) use ($store) {
                    $query->whereHas('stores', function ($query) use ($store) {
                        $query->where([
                            'stores.id'=>$store->id,
                            'stores.deleted_at'=>null
                            ]);
                    });
                })
            );
        }

        return  $tabs;
    }
}
