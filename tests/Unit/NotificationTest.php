<?php

namespace Tests\Unit;

use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\NotificationSetting;
use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function test_notification_not_sent_as_price_was_not_crawled()
    {
        $this->notification_service->new_link->price = 0;
        $this->notification_service->new_link->used_price = 0;
        $this->assertFalse($this->notification_service->check(), 'No Price was crawled');
    }

    public function test_notification_not_sent_product_is_snoozed()
    {
        $this->notification_service->new_link->price = 10000;

        $this->notification_service->product->snoozed_until = now()->addDay();

        $this->assertFalse($this->notification_service->check(), 'Product Is Snoozed');
    }

    public function test_notification_not_sent_as_price_didnt_change()
    {
        $this->notification_service->new_link->price = $this->notification_service->old_link->price;

        $this->assertFalse($this->notification_service->check(), 'Price did not change');
    }

    public function test_notification_not_sent_as_is_official_is_false()
    {
        $this->notification_service->new_link->is_official = false;
        $this->notification_service->notification_setting->is_official = true;

        $this->notification_service->new_link->price = 1000;

        $this->assertFalse($this->notification_service->check(), 'Product is not official');
    }

    public function test_notification_sent_because_user_opted_for_any_price_change()
    {
        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->any_price_change = false;

        $this->notification_service->new_link->price = 1000;
        $this->notification_service->check();
        self::assertNotContains("price changed",
            $this->notification_service->notification_reasons,
            "confirming that it doesn't trigger if user didn't opt for any price change");

        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->any_price_change = true;
        $this->assertTrue($this->notification_service->check(), 'User opted for any price change');
        $this->assertContains("price changed", $this->notification_service->notification_reasons);

        $this->notification_service->notification_reasons = [];
        $this->notification_service->new_link->price = 1;
        $this->assertTrue($this->notification_service->check(), 'User opted for any price change');
        $this->assertContains("price changed", $this->notification_service->notification_reasons);
    }

    public function test_notification_sent_because_stock_alarm()
    {
        $this->notification_service->notification_setting->is_in_stock = true;

        $this->notification_service->new_link->is_in_stock = false;
        $this->notification_service->check();
        self::assertNotContains('in stock', $this->notification_service->notification_reasons, "Doesn't trigger as it's not in stock");

        $this->notification_service->notification_reasons = [];
        $this->notification_service->new_link->is_in_stock = true;
        $this->notification_service->check();

        self::assertNotContains('in stock', $this->notification_service->notification_reasons);
    }

    public function test_notification_sent_price_lowest_in_x_days()
    {

        $this->notification_service->old_link->price = 100;
        $this->notification_service->new_link->price = 90;

        // make sure notification is sent only once per day. unless price goes lower later on in the day.
        $historyForToday = LinkHistory::updateOrCreate([
            'date' => today(),
            'link_id' => $this->notification_service->old_link->id,
        ], [
            'price' => 90,
            'used_price' => 100,
        ]
        );

        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->price_lowest_in_x_days = 100;
        $this->notification_service->check();

        self::assertNotContains("price is lowest since {$this->notification_service->notification_setting->price_lowest_in_x_days} days",
            $this->notification_service->notification_reasons);

        $historyForToday->delete();

        // new price is higher than history
        LinkHistory::factory()
            ->forEachSequence(
                [
                    'link_id' => $this->notification_service->old_link->id,
                    'price' => $this->notification_service->new_link->price - 30,
                    'date' => today(),
                    'used_price' => $this->notification_service->new_link->price + 10,
                ],
                [
                    'link_id' => $this->notification_service->old_link->id,
                    'price' => $this->notification_service->new_link->price - 30,
                    'date' => today()->subDay(),
                    'used_price' => $this->notification_service->new_link->price + 10,
                ],
                [
                    'link_id' => $this->notification_service->old_link->id,
                    'price' => $this->notification_service->new_link->price - 50,
                    'date' => today()->subDays(2),
                    'used_price' => $this->notification_service->new_link->price + 100,
                ],

                [
                    'link_id' => $this->notification_service->old_link->id,
                    'price' => $this->notification_service->new_link->price - 20,
                    'date' => today()->subDays(3),
                    'used_price' => $this->notification_service->new_link->price + 20,
                ],

            )
            ->create();

        for ($i = 1; $i < 4; $i++) {
            $this->notification_service->notification_reasons = [];
            $this->notification_service->notification_setting->price_lowest_in_x_days = $i;

            $this->notification_service->check();
            self::assertNotContains("price is lowest since {$this->notification_service->notification_setting->price_lowest_in_x_days} days",
                $this->notification_service->notification_reasons,
                "Doesn't trigger as new price is larger");
        }

        // equality check
        $this->notification_service->new_link->price -= 50;
        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->price_lowest_in_x_days = 4;
        $this->notification_service->check();

        self::assertContains("price is lowest since {$this->notification_service->notification_setting->price_lowest_in_x_days} days", $this->notification_service->notification_reasons);

        // less thab check
        $this->notification_service->new_link->price -= 60;
        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->price_lowest_in_x_days = 4;
        $this->notification_service->check();

        self::assertContains("price is lowest since {$this->notification_service->notification_setting->price_lowest_in_x_days} days",
            $this->notification_service->notification_reasons);

    }

    public function test_notification_sent_price_reached_desired_value()
    {
        $notification_settings = NotificationSetting::withoutGlobalScopes()
            ->first();

        User::where('id', $notification_settings->user_id)
            ->update(['currency_id' => null]);

        $this->notification_service->old_link->loadMissing('store', 'store.currency');

        $new_link = new Link([
            'price' => 90,
            'used_price' => 100,
            'key' => '12345',
            'name' => 'Test Product',
            'shipping_price' => 10,
            'is_official' => true,
            'is_in_stock' => true,
        ]);
        $notification_settings->price_desired = 80;
        $this->notification_service->old_link->price = 100;
        $new_service = new NotificationService(
            $this->notification_service->old_link,
            $new_link,
            $notification_settings,
            Product::withoutGlobalScopes()->first()
        );

        $new_service->check();

        self::assertNotContains("price reached desired value ({$new_service->notification_setting->price_desired}) {$new_service->old_link->store->currency->code}",
            $new_service->notification_reasons,
            "Doesn't trigger as new price is larger");

        unset($new_service);

        $notification_settings = NotificationSetting::withoutGlobalScopes()->first();

        $new_link = new Link([
            'price' => 70,
            'used_price' => 100,
            'key' => '12345',
            'name' => 'Test Product',
            'shipping_price' => 10,
            'is_official' => true,
            'is_in_stock' => true,

        ]);
        $notification_settings->price_desired = 80;
        $this->notification_service->old_link->price = 100;
        $new_service = new NotificationService(
            $this->notification_service->old_link,
            $new_link,
            $notification_settings,
            Product::withoutGlobalScopes()->first()
        );

        $new_service->check();

        self::assertContains("price reached desired value ({$new_service->notification_setting->price_desired}) {$this->notification_service->old_link->store->currency->code}",
            $new_service->notification_reasons);

        User::where('id', $notification_settings->user_id)
            ->update(['currency_id' => 1]);

    }

    public function test_notification_sent_price_dropped_percentage()
    {

        $this->notification_service->old_link->price = 100;
        $this->notification_service->new_link->price = 90;

        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->percentage_drop = 15;
        $this->notification_service->check();

        self::assertNotContains("price dropped {$this->notification_service->notification_setting->percentage_drop}%",
            $this->notification_service->notification_reasons,
            "Doesn't trigger as new price is larger");

        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->percentage_drop = 5;
        $this->notification_service->check();

        self::assertContains("price dropped {$this->notification_service->notification_setting->percentage_drop}%",
            $this->notification_service->notification_reasons);

        $this->notification_service->notification_reasons = [];
        $this->notification_service->notification_setting->percentage_drop = 10;
        $this->notification_service->check();

        self::assertContains("price dropped {$this->notification_service->notification_setting->percentage_drop}%",
            $this->notification_service->notification_reasons);

    }

    public function test_notification_count_is_cleared_daily()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'user_id' => 1,
            'max_notifications_daily' => 5,
            'notifications_sent' => 4,
        ]);

        Artisan::call('notification:clear');

        $product->refresh();

        self::assertEquals(0, $product->notifications_sent);

    }

    public function test_notification_with_extra_costs_added()
    {
        // confirm the extra cost notification
        $this->notification_service->notification_setting->price_desired = 1210;
        $this->notification_service->notification_setting->extra_costs_amount = 120;
        $this->notification_service->notification_setting->extra_costs_percentage = 9;
        $this->notification_service->new_price = 1000;
        $this->notification_service->new_link->price = 1000;

        $this->notification_service->notification_reasons = [];

        $this->notification_service->check();

        self::assertContains("price reached desired value ({$this->notification_service->notification_setting->price_desired}) {$this->notification_service->old_link->store->currency->code}",
            $this->notification_service->notification_reasons);

        // confirm the extra cost notification is not sent if the price is not reached
        $this->notification_service->notification_setting->price_desired = 1209;

        $this->notification_service->notification_reasons = [];

        $this->notification_service->check();

        self::assertNotContains("price reached desired value ({$this->notification_service->notification_setting->price_desired}) {$this->notification_service->old_link->store->currency->code}",
            $this->notification_service->notification_reasons);

    }
}
