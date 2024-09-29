<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Helpers\StoreHelper;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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

        $stores=StoreHelper::get_stores_active_for_tabs();

        if (sizeof($stores)){
            $tabs['all'] = Tab::make();
            foreach ($stores as $store)
                $tabs[$store->name]=Tab::make()->modifyQueryUsing(function (Builder $query) use ($store) {
                    $query->whereHas('stores', function ($query) use ($store) {
                        $query->where('stores.id',$store->id);
                    });
                });

            return $tabs;
        }


        return  [];


    }
}
