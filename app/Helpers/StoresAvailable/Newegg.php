<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Throwable;

class Newegg extends StoreTemplate
{
    const string GLOBAL_URL = "https://store/global/country-en/p/product_id";

    const string SINGLE_STORE_URL = "https://store/p/product_id";

    private $center_column;

    private $right_column;

    private $product_schema;

    public static array $country_short = [
        'USA' => 'us',
        'Argentina' => 'ar',
        'Australia' => 'au',
        'Bahrain' => 'bh',
        'Canada' => 'ca',
        'Hong Kong' => 'hk',
        'Occupied Palestine' => 'il',
        'Japan' => 'jp',
        'Kuwait' => 'kw',
        'Mexico' => 'mx',
        'New Zealand' => 'nz',
        'Oman' => 'om',
        'Philippines' => 'ph',
        'Qatar' => 'qa',
        'Saudi Arabia' => 'sa',
        'Singapore' => 'sg',
        'South Korea' => 'kr',
        'Thailand' => 'th',
        'UAE' => 'ae',
        'UK' => 'uk',
    ];

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
            $this->center_column = $this->xml->xpath("//div[contains(@class , 'product-main')]")[0];
            $this->right_column = $this->xml->xpath("//div[contains(@class , 'product-buy-box')]")[0];
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
            $this->name = $this->product_schema['name'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Product Name First Method", $exception->getMessage());
        }

        try {
            $this->name = $this->center_column->xpath('//h1')[0]->__toString();
        } catch (Throwable $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
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
            $this->image = $this->center_column->xpath("//img[@class='product-view-img-original']")[0]->attributes()['src']->__toString();
        } catch (Throwable $exception) {
            $this->log_error("Product Image Second Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {

        try {
            $this->price = (float) $this->product_schema['offers']['price'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price First Method", $exception->getMessage());
        }

        // method 2 to return the price of the product
        try {
            $price = $this->right_column->xpath("//div[@class='price-current']")[0];
            $this->price = (float) "{$price->strong->__toString()}{$price->sup->__toString()}";

            return;
        } catch (Throwable $exception) {
            $this->log_error("Price Second Method", $exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = Str::contains($this->product_schema['offers']['availability'], "InStock", true);
        } catch (Throwable $exception) {
            $this->log_error("Stock Availability First Method", $exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates = $this->product_schema['aggregateRating']['reviewCount'];

            return;
        } catch (Throwable $exception) {
            $this->log_error("First Method No. Of Rates", $exception->getMessage());
        }

        try {
            $this->no_of_rates = (int) GeneralHelper::get_numbers_only_with_dot($this->center_column->xpath("//div[@class='product-reviews']//span[@class='item-rating-num']")[0]->__toString());
        } catch (Throwable $exception) {
            $this->log_error("Second Method No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            $rating_section = $this->center_column->xpath("//div[@class='product-reviews']//i[contains(@class ,'rating')]")[0]
                ->attributes()['title']->__toString();
            $this->rating = (float) explode(' out', $rating_section)[0];

            return;
        } catch (Throwable $exception) {
            $this->log_error("Second Method No. Of Rates", $exception->getMessage());
        }

        try {
            $this->rating = $this->product_schema['aggregateRating']['ratingValue'];
        } catch (Throwable $exception) {
            $this->log_error("The Rate", $exception->getMessage());
        }
    }

    public function get_seller(): void
    {
        try {

            $seller_section = $this
                ->right_column
                ->xpath("//div[@class='product-seller-sold-by']//strong")[0];

            $this->seller = ($seller_section->span) ? $seller_section->span->__toString() : $seller_section->__toString();
        } catch (Throwable $exception) {
            $this->log_error("The Seller First Method", $exception->getMessage());
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        try {
            $this->condition = (Str::contains($this->product_schema['itemCondition'], "NewCondition", true)) ? "new" : "used";
        } catch (Exception $e) {
            $this->log_error("the Condition");
        }
    }

    public static function get_variations($url): array
    {
        $response = parent::get_website($url);

        parent::prepare_dom($response, $document, $xml);

        try {

            $script_content = explode("window.__initialState__ =", $document->textContent)[1];

            $main_content = explode("SocialMediaContent", $script_content)[0]."}";
            $main_content = str_replace('}]},"}', '}]}}', $main_content);

            $decoded_main_content = json_decode($main_content, true);

            $properties_with_keys = [];

            foreach ($decoded_main_content['PropertyCollection']['PropertyGroups'] as $item) {
                foreach ($item['Properties'] as $property) {
                    $properties_with_keys[$property['Value']] = $property['DisplayInfo'];
                }
            }

            $product_unique_key_with_full_name = [];

            foreach ($decoded_main_content['PropertyCollection']['AvailableFullPaths'] as $item) {
                $variables = explode(',', $item['Path']);

                $product_unique_key_with_full_name[$item['ItemNumber']] = "";

                foreach ($variables as $variable) {
                    $product_unique_key_with_full_name[$item['ItemNumber']] .= $properties_with_keys[(int) $variable]." ";
                }
            }

            return $product_unique_key_with_full_name ?? [];

        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title("Error")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        }

        return [];
    }

    public static function prepare_url($domain, $product, $store = null): string
    {
        $domain_to_use = match ($domain) {
            "newegg.ca" => self::SINGLE_STORE_URL,
            default => self::GLOBAL_URL,
        };



        return Str::replace(
            ["store", "country", "product_id"],
            [$domain, self::$country_short[Str::remove('Newegg ', $store->name)], Str::upper($product)],
            $domain_to_use);

    }

    public function is_system_detected_as_robot(): bool
    {
        return count($this->xml->xpath('//input[@id="captchacharacters"]')) ||
            count($this->xml->xpath('//label[@for="captchacharacters"]'));
    }
}
