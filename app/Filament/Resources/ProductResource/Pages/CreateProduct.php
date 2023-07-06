<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        //remove the parameters
        $no_parameter=explode("?" , $data['url'])[0];
        //get after dp
        $after_dp=explode("/dp/" , $no_parameter)[1];
        //get the first one and ignore all
        $asin=explode("/" , $after_dp);
        $data=\Arr::add($data , 'ASIN' , $asin[0]);
        return $data;
    }


}
