<?php

namespace App\Helpers;

use App\Enums\StatusEnum;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Arr;

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




        $available_stores=PriceHistory::where('product_id',$product_id)
            ->distinct()
            ->select('store_id')
            ->pluck('store_id')
            ->toArray();

        $currencies=CurrencyHelper::get_currencies();

        $stores= Store::where("status" , StatusEnum::Published)
            ->whereIn('id' , $available_stores)
            ->get(["id" , "currency_id" , "name"])
            ->keyBy("id")
            ->map(function ($record) use ($currencies){
                return [
                    "name"=>"$record->name ({$currencies[$record->currency_id]})",
                ];
            })
            ->toArray();



        $price_histories= PriceHistory::where("product_id", $product_id)
            ->whereDate("date" , ">=" , today()->subYear())
            ->orderBy("date", "desc")
            ->selectRaw(
                "store_id,
                date,
                price,
                store_id || '_' ||date  as con_date"
            )
            ->get()
            ->keyBy("con_date")
            ->toArray();


        $min_date=Carbon::parse(explode("_" , Arr::last($price_histories)["con_date"])[1]) ;
        $max_date=Carbon::parse(explode("_" , Arr::first($price_histories)["con_date"])[1]);


        $difference=$min_date->diffInDays($max_date);
        $current_date_loop=$min_date->toDateString();
        try {
            for ($i=0 ; $i<= $difference ; $i++){

                foreach ($available_stores as $single_store){
                    $current_store_date_key=$single_store . "_" . $current_date_loop;
                    if (Arr::exists($price_histories,$current_store_date_key ))
                        $stores[$single_store]["data"][]=[
                            'x'=> $price_histories[$current_store_date_key]["date"],
                            'y'=> $price_histories[$current_store_date_key]["price"],
                        ];
                    else
                        $stores[$single_store]["data"][]=[
                            'x'=> $current_date_loop,
                            'y'=>0,
                        ];
                }
                $current_date_loop= $min_date->addDay()->toDateString();


            }

        }catch (\Exception $exception){
            dd($exception->getMessage());
        }



        return $stores;
    }

}
