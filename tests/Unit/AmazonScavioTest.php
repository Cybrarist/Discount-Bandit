<?php

namespace Tests\Unit;

use App\Classes\Stores\AmazonScavio;
use App\Enums\StoreStatusEnum;
use App\Jobs\CrawlProductJob;
use App\Models\Currency;
use App\Models\Link;
use App\Models\Product;
use App\Models\Store;
use Carbon\CarbonInterface;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\StoreSeeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class AmazonScavioTest extends TestCase
{
    public function test_amazon_links_use_the_crawler_by_default(): void
    {
        $link = $this->create_amazon_link();

        Http::preventStrayRequests();
        Http::fake([
            'www.amazon.co.uk/*' => Http::response('<html><body></body></html>'),
        ]);

        $this->assertFalse(AmazonScavio::is_selected());

        $this->crawl($link);

        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://www.amazon.co.uk/dp/B0B831STBX'));
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'api.scavio.dev'));
    }

    public function test_scavio_product_is_mapped_to_the_link(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link();

        Http::preventStrayRequests();
        Http::fake([
            'api.scavio.dev/*' => Http::response($this->fixture()),
        ]);

        $this->crawl($link);

        $this->assertDatabaseHas('links', [
            'id'             => $link->id,
            'name'           => 'Tapo P110(2-pack) Smart Plug, Energy Monitoring WiFi Plug, Max 13A',
            'image'          => 'https://m.media-amazon.com/images/I/411h0Dpqo0L._SX342_SY445_QL70_ML2_.jpg',
            'price'          => 15980,
            'shipping_price' => 0,
            'rating'         => '4.6',
            'total_reviews'  => 54356,
            'seller'         => 'Amazon',
            'is_official'    => true,
            'is_in_stock'    => true,
        ]);

        $this->assertDatabaseHas('link_histories', [
            'link_id' => $link->id,
            'price'   => 15980,
        ]);
    }

    public function test_request_sends_asin_marketplace_and_bearer_key(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link('B0B831STBX/ref=sr_1_7?tag=YOURASSOCIATEID');

        Http::preventStrayRequests();
        Http::fake([
            'api.scavio.dev/*' => Http::response($this->fixture()),
        ]);

        $this->crawl($link);

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://api.scavio.dev/api/v1/amazon/product'
            && $request->hasHeader('Authorization', 'Bearer test-scavio-key')
            && $request->data() === ['query' => 'B0B831STBX', 'country' => 'gb']);
    }

    public function test_shipping_price_comes_from_the_first_delivery_option(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link();

        $response = $this->fixture();
        $response['data']['shipping']['options'][0]['price'] = 4.99;

        Http::preventStrayRequests();
        Http::fake([
            'api.scavio.dev/*' => Http::response($response),
        ]);

        $this->crawl($link);

        $this->assertDatabaseHas('links', [
            'id'             => $link->id,
            'shipping_price' => 4990,
        ]);
    }

    public function test_product_without_price_is_out_of_stock(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link();

        $response = $this->fixture();
        $response['data']['price'] = null;

        Http::preventStrayRequests();
        Http::fake([
            'api.scavio.dev/*' => Http::response($response),
        ]);

        $this->crawl($link);

        $this->assertDatabaseHas('links', [
            'id'          => $link->id,
            'price'       => 0,
            'is_in_stock' => false,
        ]);
        $this->assertDatabaseMissing('link_histories', ['link_id' => $link->id]);
    }

    public function test_every_seeded_amazon_store_has_a_marketplace(): void
    {
        $this->seed([CurrencySeeder::class, StoreSeeder::class]);

        $amazon_stores = Store::where('name', 'like', 'Amazon%')->get();

        $this->assertCount(count(AmazonScavio::MARKETPLACES), $amazon_stores);

        foreach ($amazon_stores as $store) {
            $link = new Link(['key' => 'B0B831STBX']);
            $link->setRelation('store', $store);

            $this->assertSame(AmazonScavio::MARKETPLACES[$store->domain], AmazonScavio::get_marketplace($link));
        }

        $this->assertSame('us', AmazonScavio::MARKETPLACES['amazon.com']);
        $this->assertSame('gb', AmazonScavio::MARKETPLACES['amazon.co.uk']);
        $this->assertSame('jp', AmazonScavio::MARKETPLACES['amazon.co.jp']);
    }

    public function test_api_errors_are_logged_and_nothing_is_recorded(): void
    {
        $this->use_scavio();

        $cases = [
            401 => 'API key rejected, check SCAVIO_API_KEY',
            402 => 'no credits left on the Scavio account',
            429 => 'rate limited, will try again on the next check',
            500 => 'request failed',
        ];

        Http::preventStrayRequests();
        $responses = Http::fakeSequence('api.scavio.dev/*');
        foreach (array_keys($cases) as $status) {
            $responses->push(['error' => 'test error'], $status);
        }

        foreach ($cases as $status => $reason) {
            $link = $this->create_amazon_link();

            $this->assert_crawl_logs_warning($link, "Scavio API {$status}: {$reason} (test error)");
            $this->assertDatabaseMissing('link_histories', ['link_id' => $link->id]);
        }
    }

    public function test_timeout_is_logged(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link();

        Http::preventStrayRequests();
        Http::fake([
            'api.scavio.dev/*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
        ]);

        $this->assert_crawl_logs_warning($link, 'Scavio API request failed: cURL error 28: Operation timed out');
        $this->assertDatabaseMissing('link_histories', ['link_id' => $link->id]);
    }

    public function test_missing_api_key_sends_no_request(): void
    {
        $this->use_scavio();
        config(['settings.scavio_api_key' => null]);
        $link = $this->create_amazon_link();

        Http::preventStrayRequests();
        Http::fake();

        $this->assert_crawl_logs_warning($link, 'AMAZON_PROVIDER is scavio but SCAVIO_API_KEY is empty');
        Http::assertNothingSent();
    }

    public function test_key_that_is_not_an_asin_sends_no_request(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link('not-an-asin');

        Http::preventStrayRequests();
        Http::fake();

        $this->assert_crawl_logs_warning($link, 'key is not an ASIN');
        Http::assertNothingSent();
    }

    public function test_link_is_due_again_after_the_refresh_interval(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link();

        $this->assertTrue(AmazonScavio::is_due($link), 'a link that was never fetched is due');

        $this->set_last_fetch($link, now()->subHours(23));
        $this->assertFalse(AmazonScavio::is_due($link->refresh()));

        $this->set_last_fetch($link, now()->subHours(24));
        $this->assertTrue(AmazonScavio::is_due($link->refresh()));

        config(['settings.scavio_refresh_hours' => 6]);
        $this->set_last_fetch($link, now()->subHours(7));
        $this->assertTrue(AmazonScavio::is_due($link->refresh()));
    }

    public function test_fetch_resets_the_refresh_interval_when_nothing_changed(): void
    {
        $this->use_scavio();
        $link = $this->create_amazon_link();

        Http::preventStrayRequests();
        Http::fake([
            'api.scavio.dev/*' => Http::response($this->fixture()),
        ]);

        $this->crawl($link);
        $this->set_last_fetch($link, now()->subDays(2));

        // same response again, so the link has nothing new to save
        $this->crawl($link);

        $this->assertFalse(AmazonScavio::is_due($link->refresh()));
    }

    public function test_schedule_skips_scavio_links_fetched_within_the_refresh_interval(): void
    {
        $this->use_scavio();
        [$fetched_recently, $fetched_two_days_ago, $never_fetched] = $this->create_scheduled_amazon_links();

        $dispatched = $this->run_product_schedule();

        $this->assertNotContains($fetched_recently->id, $dispatched);
        $this->assertContains($fetched_two_days_ago->id, $dispatched);
        $this->assertContains($never_fetched->id, $dispatched);
    }

    public function test_schedule_checks_every_amazon_link_with_the_crawler(): void
    {
        $links = $this->create_scheduled_amazon_links();

        $dispatched = $this->run_product_schedule();

        foreach ($links as $link) {
            $this->assertContains($link->id, $dispatched);
        }
    }

    private function use_scavio(): void
    {
        config([
            'settings.amazon_provider' => 'scavio',
            'settings.scavio_api_key'  => 'test-scavio-key',
        ]);
    }

    private function create_amazon_link(string $key = 'B0B831STBX'): Link
    {
        $store = Store::firstOrCreate(['domain' => 'amazon.co.uk'], [
            'name'        => 'Amazon UK',
            'slug'        => 'amazon_co_uk',
            'image'       => 'amazon_uk.png',
            'currency_id' => Currency::firstWhere('code', 'USD')->id,
        ]);

        $product = Product::create([
            'name'    => 'Test Product',
            'user_id' => 1,
        ]);

        return $product->links()->create([
            'key'      => $key,
            'store_id' => $store->id,
        ], ['user_id' => 1]);
    }

    /**
     * @return array<Link> fetched an hour ago, fetched two days ago, never fetched
     */
    private function create_scheduled_amazon_links(): array
    {
        // a time the default */5 schedule runs at
        $this->travelTo(now()->setTime(10, 5));

        $links = [
            $this->create_amazon_link('B0B831STBX'),
            $this->create_amazon_link('B08D9N5CK4'),
            $this->create_amazon_link('B0BTTP21QG'),
        ];

        $this->set_last_fetch($links[0], now()->subHour());
        $this->set_last_fetch($links[1], now()->subDays(2));

        Store::where('domain', 'amazon.co.uk')->update(['status' => StoreStatusEnum::Active]);

        return $links;
    }

    /**
     * @return array<int> link ids the schedule dispatched a crawl for
     */
    private function run_product_schedule(): array
    {
        Queue::fake();

        $this->artisan('schedule:run')->assertSuccessful();

        return Queue::pushed(CrawlProductJob::class)->map(fn (CrawlProductJob $job) => $job->link_id)->all();
    }

    private function set_last_fetch(Link $link, CarbonInterface $fetched_at): void
    {
        DB::table('links')->where('id', $link->id)->update([
            'created_at' => $fetched_at->copy()->subDay(),
            'updated_at' => $fetched_at,
        ]);
    }

    private function crawl(Link $link): void
    {
        (new CrawlProductJob($link->id))->handle();
    }

    private function assert_crawl_logs_warning(Link $link, string $message): void
    {
        Log::spy();

        $this->crawl($link);

        Log::shouldHaveReceived('warning')
            ->with(Mockery::any(), Mockery::on(fn ($context) => str_contains($context['exception']->getPrevious()?->getMessage() ?? '', $message)))
            ->atLeast()->once();
    }

    private function fixture(): array
    {
        return json_decode(file_get_contents(base_path('tests/Fixtures/scavio_amazon_product.json')), true);
    }
}
