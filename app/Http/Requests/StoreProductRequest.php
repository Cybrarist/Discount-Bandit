<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "url"=>["required","url"],
            "name"=>["nullable", "string"],
            "image"=>["nullable"],
            "notify_price"=>["numeric" , "nullable"],
            "official_seller"=>["boolean", "nullable"],
            "favourite"=>["boolean", "nullable" ],
            "stock_available"=>["boolean" , "nullable"],
            "lowest_within"=>["numeric" , "integer", "nullable" ],
            "number_of_rates"=>["numeric" , "integer", "nullable" ],
        ];
    }
}
