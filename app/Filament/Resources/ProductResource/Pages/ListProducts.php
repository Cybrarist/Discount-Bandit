<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\StatusEnum;
use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
        ];
    }



    public function getTabs() : array {
    //get the stores with products in

        $tabs=[
            'all' => ListRecords\Tab::make(),
        ];

        $stores=\Cache::get('stores_available');
        if (!$stores)
        {
            $stores=Store::whereNotIn('status',StatusEnum::ignored())
                ->where('tabs', true)->get();
            \Cache::forever('stores_available', $stores  );
        }

        foreach ($stores as $store)
        {
            $tabs =\Arr::add($tabs , $store->name,
                ListRecords\Tab::make()->modifyQueryUsing(function (Builder $query) use ($store) {
                    $query->whereHas('stores', function ($query) use ($store) {
                        $query->where([
                            'stores.id'=>$store->id,
                            ]);
                    });
                })
            );
        }
        return  $tabs;
    }
}
