<?php

namespace Tests\Unit;

use App\Helpers\GroupHelper;
use App\Jobs\CheckGroupPriceJob;
use App\Models\Group;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class GroupTest extends TestCase
{
    use InteractsWithDatabase, RefreshDatabase;

    public function prepare_group_with_products(): void
    {
        $this->seed([StoreSeeder::class]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => Str::password(),
        ]);

        $products = Product::factory(10)->create();

        $amazon_store = Store::where('domain', 'amazon.ae')->first();

        foreach ($products as $product) {
            $amazon_store->products()->syncWithoutDetaching([
                $product->id => ['price' => 1000 * $product->id],
            ]);
        }

        Group::create([
            'name' => 'Test Group',
            'currency_id' => $amazon_store->currency_id,
            'notify_price',
        ]);

    }

    public function test_group_price_is_least_across_multiple_keys(): void
    {
        $this->prepare_group_with_products();

        $products = Product::all();

        $group = Group::first();

        foreach ($products as $product) {
            $key = match (true) {
                $product->id < 4 => "GPU",
                $product->id >= 4 && $product->id < 7 => "CPU",
                default => null,
            };

            $group->products()->syncWithoutDetaching([
                $product->id => ['key' => $key],
            ]);
        }

        $least_value = GroupHelper::get_current_price($group);

        self::assertEquals(10 + 40 + 70, $least_value);

    }

    public function test_group_price_is_least_for_one_key_group(): void
    {
        $this->prepare_group_with_products();

        $products = Product::all();

        $group = Group::first();

        foreach ($products as $product) {
            $group->products()->syncWithoutDetaching([
                $product->id => ['key' => "single"],
            ]);
        }

        $least_value = GroupHelper::get_current_price($group);

        self::assertEquals(10, $least_value);

    }

    #[NoReturn]
    public function test_group_snoozed()
    {
        $this->prepare_group_with_products();

        $group = Group::create([
            'name' => 'Test Group snoozed',
            'currency_id' => 1,
            'snoozed_until' => today()->addDay(),
        ]);

        $group->products()->sync(Product::pluck('id')->toArray(), ['key' => 'single']);

        $job = new CheckGroupPriceJob;
        $job->handle();

        $group->refresh();

        assertEquals(0, $group->notifications_sent);

    }

    #[NoReturn]
    public function test_group_max_notification()
    {
        $this->prepare_group_with_products();

        $group = Group::create([
            'name' => 'Test Group snoozed',
            'currency_id' => 1,
            'max_notifications' => 2,
            'notifications_sent' => 2,
        ]);

        $group->products()->sync(Product::pluck('id')->toArray(), ['key' => 'single']);

        $job = new CheckGroupPriceJob;
        $job->handle();

        $group->refresh();

        assertEquals(2, $group->notifications_sent);

    }

    #[NoReturn]
    public function test_group_reached_desired_price()
    {
        $this->prepare_group_with_products();

        $group = Group::create([
            'name' => 'Test Group Reached Price',
            'currency_id' => 1,
            'notify_price' => 20,
        ]);

        $group->products()->sync(Product::pluck('id')->toArray(), ['key' => 'single']);

        $job = new CheckGroupPriceJob;
        $job->handle();

        $group->refresh();

        assertEquals(1, $group->notifications_sent);

    }

    #[NoReturn]
    public function test_group_dropped_percentage()
    {
        $this->prepare_group_with_products();

        $group = Group::create([
            'name' => 'Test Group Reached Price',
            'currency_id' => 1,
            'current_price' => 20,
            'notify_percentage' => 50,
        ]);

        $group->products()->sync(Product::pluck('id')->toArray(), ['key' => 'single']);

        $job = new CheckGroupPriceJob;
        $job->handle();

        $group->refresh();

        assertEquals(1, $group->notifications_sent);

    }
}
