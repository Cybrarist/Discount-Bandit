<?php

namespace Tests\Feature;

use App\Helpers\URLHelper;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmazonFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_key_parsed_correctly_for_amazon_ae(): void
    {

        $this->seed([StoreSeeder::class]);

        $url = "https://www.amazon.ae/Apple-iPhone-Pro-Max-256/dp/B0DGJKQ3KW";

        $url_helper = new URLHelper($url);

        self::assertEquals("B0DGJKQ3KW", $url_helper->product_unique_key);

    }
}
