<?php

namespace Tests\Unit;

use App\Helpers\LinkHelper;
use App\Models\Product;
use Tests\TestCase;

class LinkWithGetParamsTest extends TestCase
{
    public function test_link_contains_all_get_params()
    {
        \App\Models\Store::first()->update([
            'are_params_allowed' => true,
        ]);

        $store = \App\Models\Store::first();

        $product = Product::create([
            'name' => 'Test Product',
            'user_id' => 1,
            'remove_link_if_out_of_stock_for_x_days' => 10,
        ]);

        $key_with_params = 'testing-link-that-has-multiple-params?param1=1&param2=2&param3=3';

        $link = $product->links()->create([
            'price' => 100,
            'key' => $key_with_params,
            'name' => 'Test Link with params',
            'store_id' => $store->id,
        ]);

        $final_link = LinkHelper::get_url($link);

        $this->assertEquals("https://{$store->domain}/$key_with_params", $final_link);
    }

    public function test_link_allows_only_certain_params()
    {
        \App\Models\Store::first()->update([
            'are_params_allowed' => true,
            'allowed_params' => ['param1'],
        ]);

        $store = \App\Models\Store::first();

        $product = Product::create([
            'name' => 'Test Product',
            'user_id' => 1,
            'remove_link_if_out_of_stock_for_x_days' => 10,
        ]);

        $key_with_params = 'testing-link-that-has-multiple-params?param1=1&param2=2&param3=3';

        $link = $product->links()->create([
            'price' => 100,
            'key' => $key_with_params,
            'name' => 'Test Link with params',
            'store_id' => $store->id,
        ]);

        $final_link = LinkHelper::get_url($link);

        $this->assertEquals("https://{$store->domain}/testing-link-that-has-multiple-params?param1=1", $final_link);

    }
}
