<?php

namespace App\Http\Controllers\Actions\Links;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateLinkAction extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'url'],
            'product_id' => ['sometimes', 'integer', 'exists:products,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'price_desired' => ['nullable', 'numeric'],
            'percentage_drop' => ['nullable', 'numeric'],
            'lowest_within' => ['nullable', 'numeric'],
            'extra_costs_amount' => ['nullable', 'numeric'],
            'extra_costs_percentage' => ['nullable', 'numeric'],
            'is_in_stock' => ['nullable', 'boolean'],
            'is_official' => ['nullable', 'boolean'],
            'is_shipping_included' => ['nullable', 'boolean'],
            'any_price_change' => ['nullable', 'boolean'],
        ]);

        dd($validated);

    }
}
