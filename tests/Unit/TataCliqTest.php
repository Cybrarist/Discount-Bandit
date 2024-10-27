<?php

namespace Tests\Unit;

use App\Enums\StatusEnum;
use App\Helpers\StoresAvailable\Nykaa;
use App\Helpers\StoresAvailable\Tatacliq;
use App\Helpers\URLHelper;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TataCliqTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_tatacliq_is_being_crawled()
    {

        $url="https://www.tatacliq.com/asics-mens-gel-galaxy-8b-teal-running-shoes/p-mp000000022920027";

        [$product_store , $product] = $this->create_product_and_assign_it_to_store('tatacliq.com', $url );

        // crawl the product
        new Tatacliq($product_store->id);

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
