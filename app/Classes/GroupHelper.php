<?php

namespace App\Classes;

use App\Models\Currency;
use App\Models\Group;
use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupHelper
{

    public static function update_products_records($group_id , $products_repeater_fields)
    {

        foreach ($products_repeater_fields as $single_repeater_field)
            foreach ($single_repeater_field["product_id"] as $products_in_repeater_field)
                DB::table("group_product")
                    ->updateOrInsert([
                        "group_id"=>$group_id,
                        "product_id"=>$products_in_repeater_field
                    ],[
                        "key"=>Str::lower( $single_repeater_field["key"])
                    ]);
    }

    public static function update_group_product_record($group_id, $product_id , $key)
    {
        DB::table("group_product")->updateOrInsert([
            "group_id"=>$group_id,
            "product_id"=>$product_id
        ],[
            "key"=>Str::lower($key)
        ]);
    }

    public static function get_current_price(Group $group){

        $records=DB::table("group_product")->where("group_id", $group->id)
                    ->join("products" , "group_product.product_id" , "=" , "products.id")
                    ->groupBy([
                        "group_product.key",
                        "products.id"
                    ])
                    ->select([
                        "products.id as product_id",
                        "group_product.key as key",
                    ])->get();


        $product_stores= DB::table("product_store")
                            ->whereIn("product_id", $records->pluck("product_id")->toArray())
                            ->join("stores" , "stores.id" , "=" , "product_store.store_id")
                            ->where("currency_id", "=" , $group->currency_id)
                            ->get(["product_id", "store_id" , "price"])->sum("price");


        return $product_stores / 100;

    }

    public static function is_price_lower_than_notify(Group $group) {
        $current_price= self::get_current_price($group);

        return $current_price <= $group->notify_price;

    }

}
