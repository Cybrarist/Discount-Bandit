<?php

namespace App\Helpers\StoresAvailable;

use Illuminate\Support\Str;
use Throwable;

class Eprice extends StoreTemplate
{
    const string MAIN_URL = "https://store/product_id";

    private $product_schema;

    private $initial_data;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    // define crawler
    public function crawler(): void
    {
        parent::crawl_url(extra_headers: [
            'Accept' => 'application/json',
            'DNT' => 1,
            "Cache-Control" => "no-cache",
            'Sec-Fetch-User' => '1',
            "Accept-Language" => "en-US,en;q=0.5",
            'Connection' => 'keep-alive',
            "Accept-Encoding" => "gzip, deflate",
        ]);
    }

    public function prepare_sections_to_crawl(): void
    {
        if ($this->is_system_detected_as_robot()) {
            parent::crawl_url_chrome();
        }

        try {
            $this->product_schema = $this->get_product_schema(script_type: 'application/ld+json');
        } catch (Throwable $exception) {
            $this->log_error("Crawling Eprice Schema", $exception->getMessage());
        }

        try {
            $this->initial_data = json_decode($this->document->getElementById('initial-data')->attributes->getNamedItem('data-json')->textContent, true);
        } catch (Throwable $exception) {
            $this->log_error("Crawling Eprice Schema", $exception->getMessage());
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name = $this->product_schema['object']['name'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name First Method", $exception->getMessage());
        }

        try {
            $this->name = $this->initial_data["page"]["metaInfo"]["title"];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->product_schema['object']['image'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

        try {
            $this->image = $this->initial_data["page"]["product"]["media"][0]["uri"];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            $this->price = (float) $this->product_schema['object']['offers']['priceSpecification'][0]['price'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price First Method", $exception->getMessage());
        }

        try {
            $this->price = (float) $this->initial_data["page"]["product"]["price"] / 100;

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price First Method", $exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = Str::contains($this->product_schema['object']['offers']['availability'], "InStock", true);

            return;
        } catch (Throwable $exception) {
            $this->log_error("Stock Availability First Method", $exception->getMessage());
        }

    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates = (int) $this->product_schema['object']['aggregateRating']['reviewCount'];
        } catch (Throwable $exception) {
            $this->log_error("Number of Rates First Method", $exception->getMessage());
        }

        try {
            $this->no_of_rates = (int) $this->initial_data["page"]["reviews"]["reviews"];
        } catch (Throwable $exception) {
            $this->log_error("Number of Rates Second Method", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            $this->rating = $this->product_schema['object']['aggregateRating']['ratingValue'];
        } catch (Throwable $exception) {
            $this->log_error("Number of Rates First Method", $exception->getMessage());
        }

        try {
            $this->rating = $this->initial_data["page"]["reviews"]["averageRating"];
        } catch (Throwable $exception) {
            $this->log_error("Number of Rates Second Method", $exception->getMessage());
        }
    }

    public function get_seller(): void
    {
        try {
            $this->seller = $this->initial_data['page']['product']['currentOffer']['ShopName'];
        } catch (Throwable $exception) {
            $this->log_error("The Seller First Method", $exception->getMessage());
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void {}

    public static function get_variations($url): array
    {
        return [];
    }

    public static function prepare_url($domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain, $product],
            self::MAIN_URL
        );

    }

    public function is_system_detected_as_robot(): bool
    {
        return Str::contains($this->document->textContent, "Access Denied", true);
    }
}
