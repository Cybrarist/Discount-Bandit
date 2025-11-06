<?php

namespace Tests;

use App\Enums\RoleEnum;
use App\Models\Currency;
use App\Models\Link;
use App\Models\NotificationSetting;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public Product $product;

    public Link $link;

    public Link $newPriceLink;

    public NotificationService $notification_service;

    protected function setUp(): void
    {
        parent::setUp();

        Currency::factory()
            ->forEachSequence(
                [
                    'name' => 'Saudi Riyal',
                    'code' => 'SAR',
                    'symbol' => 'SAR',
                    'rate' => 3.75,
                ],
                [
                    'name' => 'Dirham',
                    'code' => 'AED',
                    'symbol' => 'AED',
                    'rate' => 3.6725,
                ],
                [
                    'name' => 'Dollar',
                    'code' => 'USD',
                    'symbol' => '$',
                    'rate' => 1,
                ],
            )
            ->create();

        User::factory()
            ->forEachSequence(
                [
                    'name' => 'Test User',
                    'email' => 'test@test.com',
                    'role' => RoleEnum::Admin,
                    'currency_id' => Currency::firstWhere('code', 'AED')->id,
                ],
                [
                    'name' => 'Test User',
                    'email' => 'test2@test.com',
                    'role' => RoleEnum::User,
                    'currency_id' => Currency::firstWhere('code', 'AED')->id,
                ],

            )
            ->create();

        Store::factory()
            ->forEachSequence([
                'name' => 'Test Store',
                'domain' => 'test.com',
                'slug' => 'test_com',
                'image' => 'test.png',
                'currency_id' => Currency::firstWhere('code', 'USD')->id,
            ], [
                'name' => 'Test Store SAR',
                'domain' => 'test.sar',
                'slug' => 'test_sar',
                'image' => 'test.png',
                'currency_id' => Currency::firstWhere('code', 'SAR')->id,
            ])
            ->create();

        Product::factory()
            ->forEachSequence(
                [
                    'name' => 'Default Product',
                    'user_id' => 1,
                ],
                [
                    'name' => 'Snoozed Product',
                    'user_id' => 1,
                    'snoozed_until' => now()->addDay(),
                ],
                [
                    'name' => 'Max Notifications Reached',
                    'max_notifications_daily' => 1,
                    'user_id' => 1,
                ]
            )
            ->create();

        // Link with current price
        Link::factory()
            ->create([
                'price' => 100,
                'used_price' => 100,
                'highest_price' => 120,
                'lowest_price' => 80,
                'store_id' => 1,
                'key' => '12345',
            ]);

        // crawled price link
        $this->newPriceLink = new Link([
            'price' => 100,
            'used_price' => 100,
            'key' => '12345',
            'name' => 'Test Product',
            'shipping_price' => 10,
            'is_official' => true,
            'is_in_stock' => true,
        ]);

        NotificationSetting::factory()
            ->create([
                'price_desired' => 0,
                'percentage_drop' => 0,
                'price_lowest_in_x_days' => 0,
                'is_in_stock' => true,
                'any_price_change' => true,
                'is_official' => true,
                'user_id' => 1,
                'extra_costs_amount' => 0,
                'extra_costs_percentage' => 0,
                'description' => 'Test Description',
                'is_shipping_included' => true,
                'link_id' => 1,
                'product_id' => 1,
            ]);

        $this->notification_service = new NotificationService(
            Link::withoutGlobalScopes()->first(),
            $this->newPriceLink,
            NotificationSetting::withoutGlobalScopes()->first(),
            Product::withoutGlobalScopes()->first()
        );

    }
}
