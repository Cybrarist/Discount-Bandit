<?php

namespace App\Classes\Stores;

use App\Helpers\LinkHelper;
use App\Models\Link;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Optional source for Amazon links: reads the product from the Scavio API (https://scavio.dev)
 * instead of loading the product page. Only used when AMAZON_PROVIDER=scavio.
 */
class AmazonScavio extends Amazon
{
    const string PRODUCT_ENDPOINT = "https://api.scavio.dev/api/v1/amazon/product";

    // store domain => marketplace code the api expects
    const array MARKETPLACES = [
        'amazon.ae'     => 'ae',
        'amazon.com'    => 'us',
        'amazon.co.uk'  => 'gb',
        'amazon.de'     => 'de',
        'amazon.fr'     => 'fr',
        'amazon.it'     => 'it',
        'amazon.sa'     => 'sa',
        'amazon.es'     => 'es',
        'amazon.pl'     => 'pl',
        'amazon.com.tr' => 'tr',
        'amazon.com.au' => 'au',
        'amazon.com.br' => 'br',
        'amazon.ca'     => 'ca',
        'amazon.cn'     => 'cn',
        'amazon.eg'     => 'eg',
        'amazon.co.jp'  => 'jp',
        'amazon.in'     => 'in',
        'amazon.com.mx' => 'mx',
        'amazon.nl'     => 'nl',
        'amazon.sg'     => 'sg',
        'amazon.se'     => 'se',
        'amazon.com.be' => 'be',
    ];

    protected array $scavio_product = [];

    public static function is_selected(): bool
    {
        return config('settings.amazon_provider') === 'scavio';
    }

    /**
     * The schedule fetches a link again only after SCAVIO_REFRESH_HOURS, since every call uses a credit.
     * A link that was never fetched is due right away; the fetch buttons are not limited.
     */
    public static function is_due(Link $link): bool
    {
        return $link->updated_at === null
            || $link->updated_at->lte($link->created_at)
            || $link->updated_at->lte(now()->subHours(config('settings.scavio_refresh_hours')));
    }

    public static function get_asin(Link $link): string
    {
        [$link_base] = LinkHelper::prepare_base_key_and_params($link);

        // keys can carry the rest of the path, like B0BTTP21QG/ref=sr_1_7
        $asin = Str::upper(Str::before($link_base, '/'));

        throw_unless(preg_match('/^[A-Z0-9]{10}$/', $asin), new RuntimeException("link {$link->id} key is not an ASIN: {$link->key}"));

        return $asin;
    }

    public static function get_marketplace(Link $link): string
    {
        $domain = $link->store->domain;

        throw_unless(array_key_exists($domain, self::MARKETPLACES), new RuntimeException("Scavio has no Amazon marketplace for {$domain}"));

        return self::MARKETPLACES[$domain];
    }

    public function crawl_product(): void
    {
        $api_key = config('settings.scavio_api_key');

        throw_if(blank($api_key), new RuntimeException("AMAZON_PROVIDER is scavio but SCAVIO_API_KEY is empty"));

        $body = [
            'query'   => static::get_asin($this->link),
            'country' => static::get_marketplace($this->link),
        ];

        // is_due() reads updated_at as the time of the last call, so set it here instead of relying on update()
        $this->link->touch();

        try {
            $response = Http::withToken($api_key)
                ->acceptJson()
                ->timeout(60)
                ->post(self::PRODUCT_ENDPOINT, $body);
        } catch (ConnectionException $exception) {
            throw new RuntimeException("Scavio API request failed: {$exception->getMessage()}", previous: $exception);
        }

        if ($response->failed()) {
            $reason = match ($response->status()) {
                401     => "API key rejected, check SCAVIO_API_KEY",
                402     => "no credits left on the Scavio account",
                429     => "rate limited, will try again on the next check",
                default => "request failed",
            };

            throw new RuntimeException("Scavio API {$response->status()}: {$reason} (" . Str::limit((string) $response->json('error', $response->body()), 200) . ")");
        }

        $this->scavio_product = $response->json('data') ?? [];

        throw_if(empty($this->scavio_product), new RuntimeException("Scavio API returned no product data"));
    }

    // the api answers with json, so there is no captcha page to work around
    public function other_method_if_system_detected_as_bot(): void {}

    public function get_name(): void
    {
        $this->product_data['name'] = $this->scavio_product['title'] ?? 'NA';
    }

    public function get_image(): void
    {
        $this->product_data['image'] = $this->scavio_product['image'] ?? '';
    }

    public function get_total_reviews(): void
    {
        $this->product_data['total_reviews'] = (int) ($this->scavio_product['reviews_count'] ?? 0);
    }

    public function get_rating(): void
    {
        $this->product_data['rating'] = (string) ($this->scavio_product['rating'] ?? '0');
    }

    public function get_seller(): void
    {
        $this->product_data['seller'] = $this->scavio_product['sold_by'] ?? '';
        $this->product_data['is_official'] = str_contains($this->product_data['seller'], 'Amazon');
    }

    public function get_price(): void
    {
        $this->product_data['price'] = (float) ($this->scavio_product['price'] ?? 0);
    }

    public function get_shipping_price(): void
    {
        $this->product_data['shipping_price'] = (float) ($this->scavio_product['shipping']['options'][0]['price'] ?? 0);
    }
}
