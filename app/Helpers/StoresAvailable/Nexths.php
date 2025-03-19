<?php

namespace App\Helpers\StoresAvailable;

use Exception;
use Illuminate\Support\Str;
use Throwable;

class Nexths extends StoreTemplate
{
    const string MAIN_URL = "https://store/Products/details/sku/product_id";

    private $product_schema;

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
        try {
            $this->product_schema = $this->xml->xpath("//div[@itemtype='http://schema.org/Product']")[0];
        } catch (Throwable $exception) {
            $this->log_error("Crawling Next H&S Schema", $exception->getMessage());
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name = $this->product_schema->xpath("//h1[@itemprop='name']")[0]->__toString();

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
        try {
            $this->name = $this->xml->xpath("//h1[@class='itempage_title']")[0]->__toString();
        } catch (Throwable $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }
    }

    public function get_image(): void
    {

        try {
            $this->image = $this->product_schema->xpath("//link[@itemprop='image']")[0]->attributes()['href']->__toString();

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

        try {
            $this->image = $this->xml->xpath("//div[@class='item active']//img")[0]->attributes()['src']->__toString();
        } catch (Throwable $exception) {
            $this->log_error("Product Image Second Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            $this->price = (float) $this->product_schema->xpath("//div[@itemprop='offers']//meta[@itemprop='price']")[0]->attributes()['content']->__toString();

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price First Method", $exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = Str::contains($this->product_schema->xpath("//div[@itemprop='offers']//meta[@itemprop='availability']")[0]->attributes()['content']->__toString(), "InStock", true);
        } catch (Throwable $exception) {
            $this->log_error("Stock Availability First Method", $exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates = (int) $this->product_schema->xpath("//div[@itemprop='aggregateRating']//meta[@itemprop='reviewCount']")[0]->attributes()['content']->__toString();
        } catch (Throwable $exception) {
            $this->log_error("Number of Rates First Method", $exception->getMessage());
        }
    }

    public function get_rate(): void {

        try {
            $this->rating = $this->product_schema->xpath("//div[@itemprop='aggregateRating']//meta[@itemprop='ratingValue']")[0]->attributes()['content']->__toString();
        } catch (Throwable $exception) {
            $this->log_error("Rating First Method", $exception->getMessage());
        }
    }

    public function get_seller(): void
    {
        try {
            $this->seller =$this->product_schema->xpath("//div[@itemprop='offers']//div[@itemprop='seller']//meta[@itemprop='name']")[0]->attributes()['content']->__toString();
        } catch (Throwable $exception) {
            $this->log_error("The Seller First Method", $exception->getMessage());
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        try {
            $this->condition = (Str::contains($this->product_schema->xpath("//div[@itemprop='offers']//meta[@itemprop='itemCondition']")[0]->attributes()['content']->__toString(), "NewCondition", true)) ? "new" : "used";
        } catch (Exception $e) {
            $this->log_error("the Condition");
        }
    }

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
        return count($this->xml->xpath('//input[@id="captchacharacters"]')) ||
            count($this->xml->xpath('//label[@for="captchacharacters"]'));
    }
}
