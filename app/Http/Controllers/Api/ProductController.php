<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ProductHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products=Product::with([
            "stores"
        ])
            ->simplePaginate(30);

        return ProductResource::collection($products);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {

        //load product so the user can edit the data related.
        $product->loadMissing(["product_stores"=>["store"]]);
        //load product history prices so the user can


        return ProductResource::make($product);


        $product_histories_per_store=ProductHelper::get_product_history_per_store(product_id: $product->id);

        dd($product_histories_per_store);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
