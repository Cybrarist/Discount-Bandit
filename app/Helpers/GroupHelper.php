<?php

namespace App\Helpers;

use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupHelper
{
    public static function sync_products_available_with_group(Group $group, array $products_available)
    {

        foreach ($products_available as $key => $product_available) {

            DB::table('group_product')
                ->where('group_id', $group->id)
                ->where('key', $key)
                ->whereNotIn('product_id', $products_available)
                ->get();

        }

    }

    public static function update_products_records($group_id, $products_repeater_fields): void
    {

        foreach ($products_repeater_fields as $single_repeater_field) {
            foreach ($single_repeater_field["product_id"] as $products_in_repeater_field) {
                DB::table("group_product")
                    ->updateOrInsert([
                        "group_id" => $group_id,
                        "product_id" => $products_in_repeater_field,
                    ], [
                        "key" => Str::lower($single_repeater_field["key"]),
                    ]);
            }
        }
    }

    public static function update_group_product_record($group_id, $product_id, $key): void
    {
        DB::table("group_product")->updateOrInsert([
            "group_id" => $group_id,
            "product_id" => $product_id,
        ], [
            "key" => Str::lower($key),
        ]);
    }

    public static function get_current_price(Group $group): int | float
    {

        return Group::where("groups.id", $group->id)
            ->join('group_product', 'group_product.group_id', '=', 'groups.id')
            ->join("products", "group_product.product_id", "products.id")
            ->join('product_store', 'products.id', '=', 'product_store.product_id')
            ->whereIn('product_store.store_id', StoreHelper::get_stores_with_same_currency($group->currency_id)->pluck("id")->toArray())
            ->select(["group_product.key", DB::raw('MIN(product_store.price)/100 as min_price')])
            ->groupBy(["group_product.key"])
            ->get()
            ->sum("min_price");

    }

    public static function is_price_lower_than_notify(Group $group): bool
    {
        $current_price = self::get_current_price($group);

        return $current_price <= $group->notify_price;

    }
}
