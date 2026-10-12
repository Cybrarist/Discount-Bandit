<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // product info
            'product_name' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'string', 'exists:products,id'],

            // link info
            'url' => ['required', 'string', 'url'],
            'price_desired' => ['nullable', 'numeric', 'min:0'],
            'percentage_drop' => ['nullable', 'numeric', 'min:0'],
            'price_lowest_in_x_days' => ['nullable', 'numeric', 'min:0'],
            'extra_costs_amount' => ['nullable', 'numeric'],
            'extra_costs_percentage' => ['nullable', 'numeric'],
            'is_in_stock' => ['nullable', 'boolean'],
            'is_official' => ['nullable', 'boolean'],
            'is_shipping_included' => ['nullable', 'boolean'],
            'any_price_change' => ['nullable', 'boolean'],
        ];
    }
}
