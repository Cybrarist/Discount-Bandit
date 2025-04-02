<?php

namespace App\Http\Controllers;

use App\Helpers\ProductHelper;
use App\Helpers\URLHelper;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request)
    {
        $request->validated();

        try {
            $url = new URLHelper($request->url);

            // make sure the store exists
            $store = Store::where("domain", $url->domain)->first();
            if (! $store) {
                abort(400, "Store doesn't exist in the database, please make sure you have the right store");
            }

            // check the product doesn't exist already
            $product_store = ProductStore::where([
                "store_id" => $store->id,
                "key" => $url->product_unique_key,
            ])->first();

            if ($product_store) {
                $product = Product::where("id", $product_store->product_id)->update([
                    "name" => $request->name,
                    "image" => $request->image,
                    "favourite" => $request->favourite,
                    "only_official" => $request->official_seller,
                    "stock" => $request->stock_available,
                    "lowest_within" => $request->lowest_within,
                ]);

                $product_id = $product_store->product_id;
            } else {
                $product = Product::create([
                    "name" => $request->name,
                    "image" => $request->image,
                    "favourite" => $request->favourite,
                    "only_official" => $request->official_seller,
                    "stock" => $request->stock_available,
                    "lowest_within" => $request->lowest_within,
                ]);
                $product_id = $product->id;
            }

            ProductStore::updateOrCreate([
                "store_id" => $store->id,
                "product_id" => $product_id,
                "key" => $url->product_unique_key,
            ], [
                "notify_price" => $request->notify_price,
                "number_of_rates" => $request->number_of_rates,
                "price" => $request->price,
            ]);

            return response([
                "message" => "Product Added / Updated Successfully",
                "link" => \App\Filament\Resources\ProductResource::getUrl("edit", ["record" => $product_id]),
            ], 200);

        } catch (\Exception $e) {
            Log::error($e);

            return response(["message" => "Something Wrong Happened".$e->getMessage()], 500);
        }
    }

    public function show(Product $product)
    {
        $product_histories_per_store = ProductHelper::get_product_history_per_store(product_id: $product->id);

        // get the prices across the stores where the product has been crawled.
        $product_stores = ProductStore::where("product_id", $product->id)
            ->get(["id", "seller", "highest_price", "lowest_price", "price", "store_id", "updated_at", "key"]);

        $all_stores_current_prices = [];
        foreach ($product_stores as $single_product_store) {
            if (Arr::exists($product_histories_per_store, $single_product_store->store_id)) {
                $all_stores_current_prices[$single_product_store->store_id] = [
                    "highest_price" => $single_product_store->highest_price,
                    "lowest_price" => $single_product_store->lowest_price,
                    "current_price" => $single_product_store->price,
                    "seller" => $single_product_store->seller,
                    "key" => $single_product_store->key,
                ];
            }

        }

        return view('products.show', [
            "prices" => $all_stores_current_prices,
            "series" => json_encode(array_values($product_histories_per_store)),
            "product" => $product,
            "product_stores" => $product_stores,
        ]);
    }

    public function snooze(Product $product)
    {

        $product->update(['snoozed_until' => today()->addDay()]);

        return "Product Snoozed Successfully Until ".today()->addDay()->toDateTimeString();
    }
}
