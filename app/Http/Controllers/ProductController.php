<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['required', 'string'],
        ]);

        return Product::whereLike('name', "%{$validated['search']}%")
            ->pluck('name', 'id');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->products()
            ->create($validated);

        return response()->json(['message' => 'Product Created Successfully']);
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
