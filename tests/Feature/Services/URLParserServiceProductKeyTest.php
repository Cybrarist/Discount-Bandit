<?php

namespace Tests\Feature\Services;

use App\Http\Controllers\Actions\Links\AttachLinkToProductAction;
use App\Models\Currency;
use App\Models\Link;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\URLParserService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class URLParserServiceProductKeyTest extends TestCase
{
    public function test_amazon_key_has_no_query_or_question_mark(): void
    {
        $this->create_store('amazon.com');
        $this->actingAs(User::first());

        $service = new URLParserService;
        $service->setup('https://www.amazon.com/Colgate-Toothbrush/dp/B01FXI8J6U?refinements=p_36%3A-6900&s=health');

        $this->assertSame('B01FXI8J6U', $service->product_key);

        $link = (new AttachLinkToProductAction)(Product::first(), [
            'store_id' => $service->store->id,
            'key'      => $service->product_key,
        ]);

        $this->assertDatabaseHas('links', [
            'id'       => $link->id,
            'store_id' => $service->store->id,
            'key'      => 'B01FXI8J6U',
        ]);
    }

    public function test_amazon_short_url_is_expanded_before_parsing_and_saving(): void
    {
        $this->create_store('amazon.com', ['short_url_hosts' => ['a.co']]);
        $this->actingAs(User::first());

        Http::preventStrayRequests();
        Http::fake([
            'a.co/*' => Http::response('', 301, ['Location' => 'https://www.amazon.com/dp/B0DS6WTXGP?ref=cm_sw_r_cp_ud_dp&social_share=cm_sw_r_cp_ud_dp']),
        ]);

        $service = new URLParserService;
        $service->setup('https://a.co/d/53K5MPR');

        $this->assertSame('B0DS6WTXGP', $service->product_key);

        $link = (new AttachLinkToProductAction)(Product::first(), [
            'store_id' => $service->store->id,
            'key'      => $service->product_key,
        ]);

        $this->assertDatabaseHas('links', [
            'id'       => $link->id,
            'store_id' => $service->store->id,
            'key'      => 'B0DS6WTXGP',
        ]);
        Http::assertSentCount(1);
    }

    public function test_short_url_lookup_is_refreshed_when_store_hosts_change(): void
    {
        $this->create_store('amazon.com');

        $this->assertFalse(URLParserService::is_short_url('https://a.co/d/53K5MPR'));

        Store::firstWhere('domain', 'amazon.com')->update(['short_url_hosts' => ['a.co']]);

        $this->assertTrue(URLParserService::is_short_url('https://a.co/d/53K5MPR'));

        Store::firstWhere('domain', 'amazon.com')->update(['short_url_hosts' => []]);

        $this->assertFalse(URLParserService::is_short_url('https://a.co/d/53K5MPR'));
    }

    public function test_custom_store_keeps_only_its_allowed_params_in_parser_and_database(): void
    {
        $this->create_store('test.com', ['are_params_allowed' => true,
                                         'allowed_params'     => [
                                             'id',
                                             'color'
                                         ]
        ]);
        $this->actingAs(User::first());

        $service = new URLParserService;
        $service->setup('https://test.com/product.php?id=5&utm_source=mail&color=red');

        $this->assertSame('product.php?id=5&color=red', $service->product_key);

        $link = (new AttachLinkToProductAction)(Product::first(), [
            'store_id' => $service->store->id,
            'key'      => $service->product_key,
        ]);

        $this->assertDatabaseHas('links', [
            'id'       => $link->id,
            'store_id' => $service->store->id,
            'key'      => 'product.php?id=5&color=red',
        ]);

        $service->setup('https://test.com/product.php?id=5&color=red&utm_source=newsletter');

        $this->assertSame($link->id, Link::withoutGlobalScopes()
            ->where('store_id', $service->store->id)
            ->where('key', $service->product_key)
            ->sole()->id);
    }

    public function test_custom_store_without_allowed_params_has_no_question_mark(): void
    {
        $this->create_store('test.com', ['are_params_allowed' => false]);

        $service = new URLParserService;
        $service->setup('https://test.com/product?utm_source=mail');

        $this->assertSame('product', $service->product_key);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function create_store(string $domain, array $attributes = []): void
    {
        Store::updateOrCreate(['domain' => $domain], [
            'name'        => $domain,
            'slug'        => str_replace('.', '_', $domain),
            'image'       => 'store.png',
            'currency_id' => Currency::firstWhere('code', 'USD')->id,
            ...$attributes,
        ]);
    }
}
