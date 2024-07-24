<?php

namespace App\Helpers;

use App\Enums\StatusEnum;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Number;

class ProductHelper
{

    public static function prepare_multiple_prices_in_table(Product $record){

        $currencies=CurrencyHelper::get_currencies();


        $prices= $record->stores->map(function ($model) use ($currencies) {
            if ($model['pivot']['price'] <= $model->pivot->notify_price)
                $color_string="green";
            else
                $color_string="red";

            return  "<span  style='color:$color_string'>" . $currencies[$model->currency_id] . \Illuminate\Support\Number::format($model->pivot->price / 100  , maxPrecision: 2) ." </span>";}
        )->implode("<br>");

        return  \Illuminate\Support\Str::of($prices)->toHtmlString();
    }

    public static function prepare_multiple_notify_prices_in_table(Product $record): \Illuminate\Support\HtmlString
    {

        $currencies=CurrencyHelper::get_currencies();

        $notify_prices= $record->stores->map(function ($model) use ($currencies) {
            return  $currencies[$model->currency_id]  .
                Number::format( $model->pivot->notify_price / 100 , maxPrecision: 2)
                . " " ;
        })->implode("<br>");

        return  \Illuminate\Support\Str::of($notify_prices)->toHtmlString();
    }


    public static function get_product_history_per_store ($product_id): array
    {
        $price_histories= PriceHistory::where("product_id", $product_id)
            ->groupBy(["date" , "store_id" ,  "price"])
            ->whereDate("date" , ">=" , today()->subYear())
            ->select([
                "store_id",
                "date as x",
                "price as y"
            ])
            ->get();

        $stores= Store::where("status" , StatusEnum::Published)
            ->whereIn('id' , $price_histories->pluck("store_id"))
            ->get(["id" , "currency_id" , "name"])
            ->keyBy("id")
            ->map(function ($record){
                $currency=CurrencyHelper::get_currencies($record->currency_id);
                return [
                    "name"=>"$record->name ({$currency})",
                ];
            })
            ->toArray();

        foreach ($price_histories as $single_price_history)  {
            $stores[$single_price_history->store_id]["data"][]=[
                'x'=>$single_price_history->x,
                'y'=>Number::format( $single_price_history->y / 100 , 2)
            ];
        }

        return $stores;
    }

}
