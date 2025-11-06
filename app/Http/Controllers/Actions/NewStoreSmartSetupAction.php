<?php

namespace App\Http\Controllers\Actions;

use App\Classes\Crawler\ChromiumCrawler;
use App\Http\Controllers\Controller;
use App\Services\SchemaParser;
use HeadlessChromium\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

class NewStoreSmartSetupAction extends Controller
{
    public array $settings = [
        'store_name' => '',
        'domain' => '',
        'slug' => '',
        'crawling_method' => "chromium",
        'extra_headers' => [],
        'user_agents' => [],
    ];

    public array $selectors = [
        'name_selectors' => [],
        'image_selectors' => [],
        'total_reviews_selectors' => [],
        'rating_selectors' => [],
        'price_selectors' => [],
        'used_price_selectors' => [],
        'shipping_price_selectors' => [],
        'stock_selectors' => [],
        'condition_selectors' => [],
    ];

    public array $schema_keys = [
        'name_schema_key' => [],
        'image_schema_key' => [],
        'total_reviews_schema_key' => [],
        'rating_schema_key' => [],
        'price_schema_key' => [],
        'used_price_schema_key' => [],
        'shipping_schema_key' => [],
        'stock_schema_key' => [],
        'condition_schema_key' => [],

    ];

    public array $schema = [];

    public function __construct(string $url, int $timeout = 5000, string $page_event = Page::NETWORK_IDLE)
    {
        $parsed_url = Uri::of($url);

        $this->settings['domain'] = Str::of($parsed_url->host())
            ->remove(["www."])
            ->toString();

        $this->settings['slug'] = str_replace(".", "_", $this->settings['domain']);

        $dom = new ChromiumCrawler(url: $url, timeout_ms: $timeout, page_event: $page_event)->dom;

        $dom->saveHTMLFile('custom.html');

        $this->build_schema_keys($dom);
        $this->build_css_selectors($dom);
    }

    public function build_schema_keys($dom): void
    {
        $this->schema = new SchemaParser($dom)->schema;

        if (isset($this->schema['product'])) {
            $this->schema = $this->schema['product'];
        }

        if (isset($this->schema['name'])) {
            $this->schema_keys['name_schema_key'][] = 'name';
        }

        $schema_images_key = [
            'image',
            'images',
            'image.0',
            'images.0',
            'offers.image',
            'offers.images',
            'offers.image.0',
            'offers.images.0',
        ];

        foreach ($schema_images_key as $key) {
            if (Arr::get($this->schema, $key)) {
                $this->schema_keys['image_schema_key'][] = $key;
            }
        }
        $schema_total_reviews_key = [
            'aggregateRating.reviewCount',
            'offers.aggregateRating.reviewCount',
            'offers.0.aggregateRating.reviewCount',
        ];

        foreach ($schema_total_reviews_key as $key) {
            if (Arr::get($this->schema, $key)) {
                $this->schema_keys['total_reviews_schema_key'][] = $key;
            }
        }
        $schema_rating_key = [
            'aggregateRating.ratingValue',
            'offers.aggregateRating.ratingValue',
            'offers.0.aggregateRating.ratingValue',
        ];

        foreach ($schema_rating_key as $key) {
            if (Arr::get($this->schema, $key)) {
                $this->schema_keys['rating_schema_key'][] = $key;
            }
        }

        $schema_price_key = [
            'price',
            'offers.price',
            'offers.0.price',
        ];

        foreach ($schema_price_key as $key) {
            if (Arr::get($this->schema, $key)) {
                $this->schema_keys['price_schema_key'][] = $key;
            }
        }

        $schema_stock_key = [
            'availability',
            'offers.availability',
            'offers.0.availability',
        ];

        foreach ($schema_stock_key as $key) {
            if (Arr::get($this->schema, $key)) {
                $this->schema_keys['stock_schema_key'][] = $key;
            }
        }
    }

    public function build_css_selectors($dom): void
    {

        // name selector
        $selectors = [
            'title',
            'meta[name="title"]',
            '[property="og:description"]',
            '[property="og:title"]',
            'h1:first-of-type',
        ];

        $attributes = [
            'content',
        ];

        $this->check_if_selectors_exist($dom, $selectors, $attributes, 'name_selectors');

        // image selector
        $selectors = [
            "meta[property='og:image']",
            "meta[property='twitter:image']",
            "meta[name='og_image']",
            "meta[itemprop='image']",
        ];

        $attributes = [
            'src',
            'href',
            'content',
        ];

        $this->check_if_selectors_exist($dom, $selectors, $attributes, 'image_selectors');

    }

    private function check_if_selectors_exist($dom, $selectors, $attributes, $key)
    {
        // go through each selector, if it has a text content then add it to the selectors array
        // if it has an attribute then add it to the selectors array

        foreach ($selectors as $selector) {
            $result = $dom->querySelector($selector);

            if (! $result) {
                continue;
            }

            if ($result->textContent) {
                $this->selectors[$key][] = $selector;
            } else {
                foreach ($attributes as $attribute) {
                    if ($result->getAttribute($attribute)) {
                        $this->selectors[$key][] = $selector;
                    }
                }
            }
        }

    }
}
