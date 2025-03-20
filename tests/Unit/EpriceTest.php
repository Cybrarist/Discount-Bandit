<?php

namespace Tests\Unit;

use App\Enums\StatusEnum;
use App\Helpers\StoresAvailable\Eprice;
use App\Helpers\StoresAvailable\Nexths;
use App\Helpers\URLHelper;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpriceTest extends TestCase
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

    public function test_eprice_uae_is_being_crawled()
    {
        $url = "https://www.eprice.it/d-68636102";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('Eprice', $url);

        // crawl the product
        new Eprice($product_store->id);

        // get the updated information
        $product->refresh();
        $product_store->refresh();

        // get main product information
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->image);

        // get product prices
        $this->assertGreaterThan(0, $product_store->price);
        $this->assertNotNull($product_store->price);
        $this->assertGreaterThan(0, $product_store->lowest_price);
        $this->assertGreaterThan(0, $product_store->highest_price);
    }
}
