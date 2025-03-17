<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use Exception;
use Illuminate\Support\Str;
use Throwable;

class Microless extends StoreTemplate
{
    const string MAIN_URL = "https://subdomain.store/product/product_id";

    private $center_column;

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
            $this->product_schema = $this->get_product_schema(script_type: 'application/ld+json');
        } catch (Throwable $exception) {
            $this->log_error("Crawling NewEgg Schema", $exception->getMessage());
        }

        try {
            $this->center_column = $this->xml->xpath("//div[@class = 'ml-home-content']")[0];
        } catch (Throwable $exception) {
            $this->log_error("Crawling NewEgg Structure", $exception->getMessage());
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name = $this->document->getElementsByTagName("title")->item(0)->textContent;

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name First Method", $exception->getMessage());
        }

        try {
            $this->name = $this->center_column->xpath("//h1[@class='product-title-h1']//span")[0]->__toString();

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }

        // product schema moved to last method as the product name is cropped in the website schema
        try {
            $this->name = $this->product_schema['name'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name Third Method", $exception->getMessage());
        }

    }

    public function get_image(): void
    {

        try {
            $this->image = $this->product_schema['image'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

        try {
            $this->image = $this->center_column->xpath("//div[@class='prod-img-pic-wrap']//a//img")[0]->attributes()['src']->__toString();

        } catch (Throwable $exception) {
            $this->log_error("Product Image Second Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {

        try {
            $this->price = (float) $this->product_schema['offers'][0]['price'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price First Method", $exception->getMessage());
        }

        // method 2 to return the price of the product
        try {
            $price = $this->center_column->xpath("//span[@class='price-amount']")[0];
            $this->price = (float) GeneralHelper::get_numbers_only_with_dot($price);

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price Second Method", $exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = Str::contains($this->product_schema['offers'][0]['availability'], "InStock", true);
        } catch (Throwable $exception) {
            $this->log_error("Stock Availability First Method", $exception->getMessage());
        }
    }

    public function get_no_of_rates(): void {}

    public function get_rate(): void {}

    public function get_seller(): void
    {
        try {

            $this->seller = $this
                ->center_column
                ->xpath("//div[@class='product-sold-by']//a")[0]->__toString();
        } catch (Throwable $exception) {
            $this->log_error("The Seller First Method", $exception->getMessage());
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        try {
            $this->condition = (Str::contains($this->product_schema['offers'][0]['itemCondition'], "NewCondition", true)) ? "new" : "used";
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
        $domain_to_use = match ($store->name) {
            "Microless UAE" => "uae",
        };

        return Str::replace(
            ["store", "subdomain", "product_id"],
            [$domain, $domain_to_use, $product],
            self::MAIN_URL
        );

    }

    public function is_system_detected_as_robot(): bool
    {
        return count($this->xml->xpath('//input[@id="captchacharacters"]')) ||
            count($this->xml->xpath('//label[@for="captchacharacters"]'));
    }
}
