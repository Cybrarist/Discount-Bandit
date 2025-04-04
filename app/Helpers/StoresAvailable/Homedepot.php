<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use Illuminate\Support\Str;
use Throwable;

class Homedepot extends StoreTemplate
{
    const string MAIN_URL = "https://www.store/p/product_id";

    private $product_schema;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    // define crawler
    public function crawler(): void
    {
        parent::crawl_url_chrome();
    }

    public function prepare_sections_to_crawl(): void
    {
        try {
            $this->product_schema = $this->get_product_schema(script_type: 'application/ld+json');

            return;
        } catch (Throwable $exception) {
            $this->log_error("Page Structure", $exception->getMessage());
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {

        try {
            $this->name = $this->product_schema['name'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name First Method", $exception->getMessage());
        }

        try {
            $this->name = trim($this->document->getElementsByTagName("title")->item(0)->textContent);

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }

    }

    public function get_image(): void
    {

        try {
            $this->image = $this->product_schema['image'][0];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

        try {
            $this->image = $this->document->getElementById("thd-helmet__meta--ogImage")->getAttribute("content");

        } catch (Throwable $exception) {
            $this->log_error("Product Image Second Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {

        try {

            $temp = $this->xml->xpath("//div[contains(@data-testid, 'sticky-nav__price-value--')]")[0]->attributes()['data-testid']->__toString();

            $this->price = (float) explode("sticky-nav__price-value--", $temp)[1];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price First Method", $exception->getMessage());
        }

        // method 2 to return the price of the product
        try {
            $price_div = $this->xml->xpath("//div[@id='was-price']//div//span");

            $this->price = (float) $price_div[1]->__toString().$price_div[2]->__toString().$price_div[3]->__toString();

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price Second Method", $exception->getMessage());
        }

        try {
            $price_div = $this->xml->xpath("//div[@id='standard-price']//div//span");

            $this->price = (float) $price_div[1]->__toString().$price_div[2]->__toString().$price_div[3]->__toString();
        } catch (Throwable $exception) {
            $this->log_error("Price Third Method", $exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = Str::contains($this->product_schema['offers']['availability'], 'InStock', true);

            return;
        } catch (Throwable $exception) {
            $this->log_error("Stock Availability First Method", $exception->getMessage());
        }

        try {
            $this->in_stock = $this->price > 0;

        } catch (Throwable $exception) {
            $this->log_error("Stock Availability Second Method", $exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates = (int) $this->product_schema['aggregateRating']['reviewCount'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("First Method No. Of Rates", $exception->getMessage());
        }

        try {
            $this->no_of_rates = (int) GeneralHelper::get_numbers_only($this->xml->xpath("//span[@id='product-details__review__target']/button/span")[1]->__toString());
        } catch (Throwable $exception) {
            $this->log_error("Second Method No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            $this->rating = $this->product_schema['aggregateRating']['ratingValue'];
        } catch (Throwable $exception) {
            $this->log_error("The Rate", $exception->getMessage());
        }

        try {
            $this->rating = Str::remove(' stars', $this->xml->xpath("//button[@data-testid='rating-button']/span")[0]->attributes()['aria-label']->__toString(), false);
        } catch (Throwable $exception) {
            $this->log_error("Second Method The Rate", $exception->getMessage());
        }

    }

    public function get_seller(): void
    {
        $this->seller = "homedepot";
    }

    public function get_shipping_price(): void {}

    // TODO: Implement get_condition() method.
    public function get_condition() {}

    public static function get_variations($url): array
    {
        return [];
    }

    public static function prepare_url($domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain, $product],
            self::MAIN_URL);
    }

    public function is_system_detected_as_robot(): bool
    {
        return false;
    }
}
