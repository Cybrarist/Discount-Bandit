<?php

namespace Tests\Unit;

use App\Classes\Stores\Amazon;
use App\Enums\CrawlingMethodEnum;
use App\Models\Currency;
use App\Models\Link;
use App\Models\Store;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Tests\TestCase;

class AmazonCrawlingMethodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::first());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function chromiumRequiredDomains(): array
    {
        return [
            'amazon.de'    => ['amazon.de'],
            'amazon.ca'    => ['amazon.ca'],
            'amazon.es'    => ['amazon.es'],
            'amazon.fr'    => ['amazon.fr'],
            'amazon.it'    => ['amazon.it'],
            'amazon.co.uk' => ['amazon.co.uk'],
        ];
    }

    #[DataProvider('chromiumRequiredDomains')]
    public function test_marketplaces_known_to_block_plain_http_use_chromium_by_default(string $domain): void
    {
        $this->assertTrue(
            $this->crawlerUsesChromium($domain),
            "{$domain} should be crawled with Chromium when the store has no explicit setting"
        );
    }

    public function test_unlisted_marketplace_keeps_plain_http_by_default(): void
    {
        $this->assertFalse($this->crawlerUsesChromium('amazon.ae'));
    }

    public function test_explicit_chromium_wins_over_an_unlisted_marketplace(): void
    {
        $this->assertTrue(
            $this->crawlerUsesChromium('amazon.ae', ['crawling_method' => 'chromium'])
        );
    }

    public function test_explicit_simple_http_wins_over_a_listed_marketplace(): void
    {
        $this->assertFalse(
            $this->crawlerUsesChromium('amazon.es', ['crawling_method' => 'http'])
        );
    }

    public function test_invalid_crawling_method_is_ignored(): void
    {
        $this->assertTrue(
            $this->crawlerUsesChromium('amazon.es', ['crawling_method' => 'not-a-method'])
        );
    }

    public function test_crawling_method_resolves_strings_and_enum_instances(): void
    {
        $this->assertSame(CrawlingMethodEnum::Chromium, CrawlingMethodEnum::fromValue('chromium'));
        $this->assertSame(CrawlingMethodEnum::SimpleHttp, CrawlingMethodEnum::fromValue('http'));
        $this->assertSame(CrawlingMethodEnum::Chromium, CrawlingMethodEnum::fromValue(CrawlingMethodEnum::Chromium));

        $this->assertNull(CrawlingMethodEnum::fromValue('not-a-method'));
        $this->assertNull(CrawlingMethodEnum::fromValue(null));
        $this->assertNull(CrawlingMethodEnum::fromValue(123));
    }

    public function test_forcing_simple_http_on_a_listed_marketplace_keeps_http_headers(): void
    {
        $crawler = $this->makeCrawler('amazon.es', ['crawling_method' => 'http']);

        $this->assertFalse($this->chromiumFlag($crawler));
        $this->assertNotSame('', (new ReflectionProperty(Amazon::class, 'user_agent'))->getValue($crawler));
        $this->assertNotEmpty((new ReflectionProperty(Amazon::class, 'extra_headers'))->getValue($crawler));
    }

    /**
     * Build an Amazon link for the given domain. The link has no products
     * attached, so StoreTemplate returns before crawling anything and the
     * resolved crawling method can be inspected without touching the network.
     */
    private function crawlerUsesChromium(string $domain, array $customSettings = []): bool
    {
        return $this->chromiumFlag($this->makeCrawler($domain, $customSettings));
    }

    private function makeCrawler(string $domain, array $customSettings = []): Amazon
    {
        $store = Store::factory()->create([
            'name'            => $domain,
            'domain'          => $domain,
            'slug'            => str_replace('.', '_', $domain),
            'image'           => 'store.png',
            'currency_id'     => Currency::firstWhere('code', 'USD')->id,
            'custom_settings' => $customSettings,
        ]);

        $link = Link::factory()->create([
            'store_id' => $store->id,
            'key'      => 'B09BQ637HP',
        ]);

        return new Amazon($link);
    }

    private function chromiumFlag(Amazon $crawler): bool
    {
        return (new ReflectionProperty(Amazon::class, 'chromium_crawler'))->getValue($crawler);
    }
}
