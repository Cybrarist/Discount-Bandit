<?php

namespace App\Http\Requests;

use App\Enums\ProductStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'name' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ProductStatusEnum::class)],
            'is_favourite' => ['required', 'boolean'],
            'snoozed_until' => ['nullable', 'date'],
            'remove_link_if_out_of_stock_for_x_days' => ['nullable', 'integer'],
        ];
    }
}
