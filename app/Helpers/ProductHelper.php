<?php

namespace App\Helpers;

use App\Enums\StatusEnum;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;

class ProductHelper
{

    public static function prepare_multiple_prices_in_table(Product $record, $currencies=[], $stores=[]): \Illuminate\Support\HtmlString
    {
        $prices="";
        foreach ($record->product_stores as $single_product_store){
            $color_string=($single_product_store->price <= $single_product_store->notify_price) ? "green" :"red";


            $prices.= "<p  style='color:$color_string'>" .
                $currencies[$stores[$single_product_store->store_id]["currency_id"]].
                \Illuminate\Support\Number::format($single_product_store->price   , maxPrecision: 2) .
                "</p>";
        }

        return  \Illuminate\Support\Str::of($prices)->toHtmlString();
    }

    public static function prepare_multiple_notify_prices_in_table(Product $record, $currencies=[], $stores=[]): \Illuminate\Support\HtmlString
    {
        $notify_prices="";
        foreach ($record->product_stores as $single_product_store)
            $notify_prices.= "<p>". $currencies[$stores[$single_product_store->store_id]["currency_id"]].
                \Illuminate\Support\Number::format($single_product_store->notify_price   , maxPrecision: 2) .
                "</p>";

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
