<?php

namespace Tests\Unit;

use App\Enums\StatusEnum;
use App\Helpers\StoresAvailable\Amazon;
use App\Helpers\StoresAvailable\Newegg;
use App\Helpers\URLHelper;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewEggTest extends TestCase
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
        $url = "https://www.newegg.com/global/ae-en/kingston-1tb-snv3s/p/N82E16820242902";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('Newegg UAE', $url);

        // crawl the product
        new Newegg($product_store->id);

        // get the updated information
        $product->refresh();
        $product_store->refresh();

        // get main product information
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->image);

        // get product prices
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->lowest_price);
        $this->assertNotNull($product_store->highest_price);
    }


    public function test_newegg_usa_is_being_crawled()
    {
        $url = "https://www.newegg.com/kingston-1tb-snv3s/p/N82E16820242902";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('Newegg USA', $url);

        // crawl the product
        new Newegg($product_store->id);

        // get the updated information
        $product->refresh();
        $product_store->refresh();

        // get main product information
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->image);

        // get product prices
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->lowest_price);
        $this->assertNotNull($product_store->highest_price);
    }

    public function test_newegg_canada_is_being_crawled()
    {
        $url = "https://www.newegg.ca/kingston-1tb-snv3s/p/N82E16820242902";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('Newegg USA', $url);

        // crawl the product
        new Newegg($product_store->id);

        // get the updated information
        $product->refresh();
        $product_store->refresh();

        // get main product information
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->image);

        // get product prices
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->lowest_price);
        $this->assertNotNull($product_store->highest_price);
    }


}
