<?php

namespace Tests\Unit;

use App\Jobs\DeleteLinksFromProductsThatAreOutOfStockForXDaysJob;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductStockRemovalTest extends TestCase
{
    public function test_links_are_not_removed_from_product_if_not_out_of_stock_for_x_days()
    {

        $product = Product::create([
            'name' => 'Test Product',
            'user_id' => 1,
            'remove_link_if_out_of_stock_for_x_days' => 10,
        ]);

        $link = $product->links()->create([
            'price' => 100,
            'key' => Str::random(),
            'name' => 'Test Product',
            'store_id' => 1,
        ]);

        $link->link_histories()->create([
            'price' => 100,
            'date' => today()->subDays(10),
            'used_price' => 100,
        ]);

        $job = (new DeleteLinksFromProductsThatAreOutOfStockForXDaysJob)->withFakeQueueInteractions();

        $job->handle();


        $link_product_count = DB::table('link_product')->where('product_id', $product->id)->count();

        self::assertEquals(1, $link_product_count);

    }

    public function test_links_are_removed_from_product_if_out_of_stock_for_x_days()
    {

        $product = Product::create([
            'name' => 'Test Product',
            'user_id' => 1,
            'remove_link_if_out_of_stock_for_x_days' => 10,
        ]);

        $link = $product->links()->create([
            'price' => 100,
            'key' => Str::random(),
            'name' => 'Test Product',
            'store_id' => 1,
        ]);

        $link->link_histories()->create([
            'price' => 100,
            'date' => today()->subDays(11),
            'used_price' => 100,
        ]);

        $job = (new DeleteLinksFromProductsThatAreOutOfStockForXDaysJob)->withFakeQueueInteractions();

        $job->handle();


        $link_product_count = DB::table('link_product')->where('product_id', $product->id)->count();

        self::assertEquals(0, $link_product_count);

    }

}
