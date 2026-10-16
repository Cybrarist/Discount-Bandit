<?php

namespace App\Jobs;

use App\Models\Currency;
use App\Models\Store;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AddStoreToDiscountJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly string $url, public readonly string $image)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::get($this->url);

        $contents = Http::get($this->image)->body();
        $image_name = Str::random(10).'.png';

        Storage::disk('public')->put('store/'.$image_name, $contents);

        $store_information = json_decode($response->body(), true);

        $custom_settings = [
            'crawling_method' => $store_information['crawling_method'],
            'timeout' => $store_information['timeout'],
            'page_event' => $store_information['page_event'],
            'name_selectors' => $store_information['css']['name_selectors'],
            'image_selectors' => $store_information['css']['image_selectors'],
            'total_reviews_selectors' => $store_information['css']['total_reviews_selectors'],
            'rating_selectors' => $store_information['css']['rating_selectors'],
            'price_selectors' => $store_information['css']['price_selectors'],
            'seller_selectors' => $store_information['css']['seller_selectors'],
            'used_price_selectors' => $store_information['css']['used_price_selectors'],
            'shipping_price_selectors' => $store_information['css']['shipping_selectors'],
            'stock_selectors' => $store_information['css']['stock_selectors'],
            'in_or_out_of_stock_selector_type' => $store_information['css']['in_or_out_of_stock_selector_type'],
            'stock_value_to_search' => $store_information['css']['stock_value_to_search'],
            'condition_selectors' => $store_information['css']['condition_selectors'],

            'name_schema_key' => $store_information['schema']['name_schema_key'],
            'image_schema_key' => $store_information['schema']['image_schema_key'],
            'total_reviews_schema_key' => $store_information['schema']['total_reviews_schema_key'],
            'rating_schema_key' => $store_information['schema']['rating_schema_key'],
            'price_schema_key' => $store_information['schema']['price_schema_key'],
            'used_schema_key' => $store_information['schema']['used_price_schema_key'],
            'shipping_schema_key' => $store_information['schema']['shipping_schema_key'],
            'stock_schema_key' => $store_information['schema']['stock_schema_key'],
            'condition_schema_key' => $store_information['schema']['condition_schema_key'],
            'seller_schema_key' => $store_information['schema']['seller_schema_key'],
        ];

        Store::withoutEvents(function () use ($store_information, $custom_settings, $image_name) {
            return Store::updateOrCreate([
                'domain' => $store_information['domain'],
            ], [
                'name' => $store_information['name'],
                'slug' => $store_information['slug'],
                'image' => $image_name,
                'custom_settings' => $custom_settings,
                'currency_id' => Currency::firstWhere('code', $store_information['currency'])->id,
                'status' => \App\Enums\StoreStatusEnum::Active->value,
            ]);
        });
    }
}
