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
        $data['url']=$no_parameter;
        //get after dp/gp
        $after_dp=explode("/dp/" , $no_parameter);
        $after_gp=explode("/gp/product/" , $no_parameter);

        if (sizeof($after_dp) > 1)
            $asin=explode("/" , $after_dp[1]);
        else if (sizeof($after_gp) > 1)
            $asin=explode("/" , $after_gp[1]);
        else
            dd("the url is different from the usual . can you open a ticket on github");

        //get the first one and ignore all
        $data=\Arr::add($data , 'ASIN' , $asin[0]);
        return $data;
    }


}
