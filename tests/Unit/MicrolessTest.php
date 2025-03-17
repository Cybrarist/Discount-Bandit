<?php

namespace Tests\Unit;

use App\Enums\StatusEnum;
use App\Helpers\StoresAvailable\Amazon;
use App\Helpers\StoresAvailable\Microless;
use App\Helpers\StoresAvailable\Newegg;
use App\Helpers\URLHelper;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MicrolessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function create_product_and_assign_it_to_store(string $name, string $url): array
    {
        $this->seed([StoreSeeder::class]);

        // get the unique key of the product
        $url_helper = new URLHelper($url);

        // create product and make sure it's added
        $product = Product::create([
            "status" => StatusEnum::Published,
        ]);

        $this->assertModelExists($product);

        // add the product store
        ProductStore::create([
            'product_id' => $product->id,
            'store_id' => Store::where('name', $name)->firstOrFail()->id,
            'key' => $url_helper->product_unique_key,
        ]);

        $product_store = ProductStore::first();
        $this->assertModelExists($product_store);

        return [$product_store, $product];

    }

    public function test_newegg_uae_is_being_crawled()
    {
        $url = "https://uae.microless.com/product/msi-advanced-gaming-pc-amd-ryzen-7-8700f-8-cores-16-threads-nvidia-rtx-4060-8gb-32gb-ddr5-ram-6000mhz-1tb-ssd-gen-4-650w-80-plus-psu-120mm-tower-cooler-wi-fi-bt-msi-gift-included/";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('Microless UAE', $url);

        // crawl the product
        new Microless($product_store->id);

        // get the updated information
        $product->refresh();
        $product_store->refresh();

        // get main product information
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->image);

        // get product prices
        $this->assertGreaterThan(0, $product_store->price);
        $this->assertGreaterThan(0, $product_store->lowest_price);
        $this->assertGreaterThan(0, $product_store->highest_price);
    }


}
