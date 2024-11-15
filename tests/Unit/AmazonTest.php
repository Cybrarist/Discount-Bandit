<?php

namespace Tests\Unit;

use App\Enums\StatusEnum;
use App\Helpers\StoresAvailable\Amazon;
use App\Helpers\URLHelper;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmazonTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic unit test example.
     */

    public function create_product_and_assign_it_to_store(string $domain, string $url)
    {
        $this->seed([StoreSeeder::class]);

        // get the unique key of the product
        $url_helper= new URLHelper($url);

        //create product and make sure it's added
        $product=Product::create([
            "status"=>StatusEnum::Published,
        ]);

        $this->assertModelExists($product);

        // add the product store
        ProductStore::create([
            'product_id'=>$product->id,
            'store_id'=>Store::where('domain', $domain)->firstOrFail()->id,
            'key'=>$url_helper->product_unique_key
        ]);

        $product_store=ProductStore::first();
        $this->assertModelExists($product_store);


        return [$product_store, $product];


    }



    public function test_amazon_uae_is_being_crawled()
    {
        $url="https://www.amazon.ae/Apple-iPhone-Pro-Max-256/dp/B0DGJKQ3KW";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('amazon.ae', $url );

        // crawl the product
        new Amazon($product_store->id);

        // get the updated information
        $product->refresh();
        $product_store->refresh();

        //get main product information
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->image);

        //get product prices
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->lowest_price);
        $this->assertNotNull($product_store->highest_price);
    }

    public function test_amazon_usa_is_being_crawled()
    {
        $url="https://www.amazon.com/SAMSUNG-Smartphone-Unlocked-Android-Titanium/dp/B0CMDWC436";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('amazon.com', $url );

        // crawl the product
        new Amazon($product_store->id);

        // get the updated information
        $product->refresh();
        $product_store->refresh();

        //get main product information
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->image);

        //get product prices
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->price);
        $this->assertNotNull($product_store->lowest_price);
        $this->assertNotNull($product_store->highest_price);
    }




}
