<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('is_favourite'),
            )
            ->allowedSorts(
                'name',
                'created_at',
                AllowedSort::field('lowest_price'),
                AllowedSort::field('highest_price'),
            )
            ->allowedIncludes('links', 'categories')
            ->defaultSort('-created_at')
            ->paginate(20)
            ->appends($request->query());

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $product = Auth::user()->products()
            ->create($validated);

        return ProductResource::make($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'links',
            'links.link_histories',
            'categories',
        ]);

        return ProductResource::make($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        //
    }

    public function snooze(int $productId)
    {
        Product::withoutGlobalScopes()
            ->where('id', $productId)
            ->update([
                'snoozed_until' => today()->addDay(),
            ]);

        return "Product Snoozed Successfully Until ".today()->addDay()->format('d-m-Y');
    }
}
