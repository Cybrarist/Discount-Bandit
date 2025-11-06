<?php

namespace Tests\Unit;

use App\Models\Currency;
use App\Models\Link;
use App\Models\NotificationSetting;
use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use Tests\TestCase;

class NotificationCurrencyTest extends TestCase
{
    public function test_prices_are_being_converted()
    {
        // the default price is 100 and 100 for both old and new links
        $old_link = Link::withoutGlobalScopes()->first();
        $new_link = new Link([
            'price' => 100,
            'used_price' => 100,
            'key' => '12345',
            'name' => 'Test Product',
            'shipping_price' => 10,
            'is_official' => true,
            'is_in_stock' => true,
        ]);

        $settings = NotificationSetting::withoutGlobalScopes()->first();

        $product = Product::withoutGlobalScopes()->first();
        $new_service = new NotificationService($old_link, $new_link, $settings, $product);

        $currencies = Currency::pluck('rate', 'code')->toArray();

        self::assertEquals(100 * $currencies['AED'], $new_service->old_price);
        self::assertEquals(100 * $currencies['AED'], $new_service->new_price);
    }

    public function test_prices_are_not_being_converted()
    {

        User::first()->update(['currency_id' => null]);
        // the default price is 100 and 100 for both old and new links
        $old_link = Link::withoutGlobalScopes()->first();
        $new_link = new Link([
            'price' => 100,
            'used_price' => 100,
            'key' => '12345',
            'name' => 'Test Product',
            'shipping_price' => 10,
            'is_official' => true,
            'is_in_stock' => true,
        ]);

        $settings = NotificationSetting::withoutGlobalScopes()->first();

        $product = Product::withoutGlobalScopes()->first();
        $new_service = new NotificationService($old_link, $new_link, $settings, $product);

        self::assertEquals(100 , $new_service->old_price);
        self::assertEquals(100 , $new_service->new_price);
    }

}
