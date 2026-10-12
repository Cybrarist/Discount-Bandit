<?php

namespace App\Http\Requests;

use App\Enums\CrawlingMethodEnum;
use App\Enums\StoreStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'domain' => ['sometimes', 'string', 'max:255'],
            'image' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('stores', 'slug')->ignore($this->route('store'))],
            'referral' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(StoreStatusEnum::class)],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'are_params_allowed' => ['nullable', 'boolean'],
            'allowed_params' => ['nullable', 'array'],
            'allowed_params.*' => ['string'],
            'custom_settings' => ['nullable', 'array'],
            'custom_settings.crawling_method' => ['nullable', Rule::enum(CrawlingMethodEnum::class)],
            'custom_settings.timeout' => ['nullable', 'integer', 'min:0'],
            'custom_settings.page_event' => ['nullable', 'string', Rule::in(['DOMContentLoaded', 'firstContentfulPaint', 'firstImagePaint', 'firstMeaningfulPaint', 'firstPaint', 'init', 'InteractiveTime', 'load', 'networkIdle'])],
            'custom_settings.extra_headers' => ['nullable', 'array'],
            'custom_settings.extra_headers.*' => ['string'],
            'custom_settings.user_agents' => ['nullable', 'array'],
            'custom_settings.user_agents.*' => ['string'],
            'custom_settings.name_selectors' => ['nullable', 'string'],
            'custom_settings.name_schema_key' => ['nullable', 'string'],
            'custom_settings.image_selectors' => ['nullable', 'string'],
            'custom_settings.image_schema_key' => ['nullable', 'string'],
            'custom_settings.total_reviews_selectors' => ['nullable', 'string'],
            'custom_settings.total_reviews_schema_key' => ['nullable', 'string'],
            'custom_settings.rating_selectors' => ['nullable', 'string'],
            'custom_settings.rating_schema_key' => ['nullable', 'string'],
            'custom_settings.price_selectors' => ['nullable', 'string'],
            'custom_settings.price_schema_key' => ['nullable', 'string'],
            'custom_settings.seller_selectors' => ['nullable', 'string'],
            'custom_settings.seller_schema_key' => ['nullable', 'string'],
            'custom_settings.used_price_selectors' => ['nullable', 'string'],
            'custom_settings.used_schema_key' => ['nullable', 'string'],
            'custom_settings.shipping_price_selectors' => ['nullable', 'string'],
            'custom_settings.shipping_schema_key' => ['nullable', 'string'],
            'custom_settings.stock_selectors' => ['nullable', 'string'],
            'custom_settings.in_or_out_of_stock_selector_type' => ['nullable', 'boolean'],
            'custom_settings.stock_value_to_search' => ['nullable', 'string'],
            'custom_settings.stock_schema_key' => ['nullable', 'string'],
            'custom_settings.condition_selectors' => ['nullable', 'string'],
            'custom_settings.condition_schema_key' => ['nullable', 'string'],
        ];
    }
}
