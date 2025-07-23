<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Store;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Noon extends StoreTemplate
{
    const MAIN_URL = "https://store/country-en/product_id/p/";

    private $schema_script;

    public function __construct(private int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    public function crawler(): void
    {
        parent::crawl_url_chrome();
    }

    public function prepare_sections_to_crawl(): void
    {
        try {
            $scripts = $this->xml->xpath("//script[@type='application/ld+json']");

            foreach ($scripts as $single_script) {
                if (Str::contains($single_script->__toString(), '"@type":"Product"')) {
                    $this->schema_script = json_decode($single_script->__toString());
                }
            }

        } catch (Error|exception) {
            $this->log_error("Crawling Noon");
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name = $this->schema_script->name;

            return;
        } catch (Error|Exception $e) {
            $this->log_error("Product Name First Method");
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->schema_script->image[0];
        } catch (Error|Exception $e) {
            $this->log_error("Product Image First Method");
        }
    }

    public function get_price(): void
    {
        try {
            $this->price = (float) $this->schema_script->offers[0]->price;

            return;
        } catch (Error|Exception) {
            $this->log_error("Price First Method");
        }
    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = Str::contains($this->schema_script->offers[0]->availability, "InStock", true);
        } catch (Exception) {
            $this->log_error("Stock Availability First Method");
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates = (int) $this->schema_script->aggregateRating->reviewCount;
        } catch (Error|Exception $e) {
            $this->log_error("No. Of Rates");
        }
    }

    public function get_rate(): void
    {
        try {
            $this->rating = $this->schema_script->aggregateRating->ratingValue;
        } catch (Error|Exception $e) {
            $this->log_error("The Rate");
        }
    }

    public function get_seller(): void
    {

        try {
            $this->seller = $this->schema_script->offers[0]->seller->name;

            return;
        } catch (Error|Exception $e) {
            $this->log_error("The Seller First Method");
        }

    }

    public function get_shipping_price(): void {}

    public function get_condition(): void
    {
        try {
            $this->condition = (Str::contains($this->schema_script->offers[0]->itemCondition, "NewCondition", true)) ? "new" : "used";
        } catch (Error|\Exception  $e) {
            $this->log_error("Condition");
        }

    }

    public static function get_variations($url): array
    {
        $response = self::get_website($url);

        self::prepare_dom($response, $document, $xml);
        try {
            $array_script = $document->getElementById("twister_feature_div")->getElementsByTagName("script");
            $array_script = $array_script->item($array_script->count() - 1)->nodeValue;
            $array_script = explode('"dimensionValuesDisplayData"', $array_script)[1];
            $array_script = explode("\n", $array_script)[0];
            $final_string = preg_replace('/\s+[\{\}\:]/', '', $array_script);
            $array_of_keys_values = explode("],", $final_string);

            foreach ($array_of_keys_values as $single) {
                $key_value = explode(":[", Str::replace(['"', ']},'], " ", $single));
                $options[Str::replace(" ", "", $key_value[0])] = $key_value[1];
            }

            return $options ?? [];

        } catch (Exception) {

            Notification::make()
                ->danger()
                ->title("Existing Product")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        }

        return [];
    }

    public static function prepare_url($domain, $product, $store = null): string
    {

        return Str::replace(
            ["store", "country", "product_id"],
            [$domain, explode(" ", $store->name)[1], Str::upper($product)],
            self::MAIN_URL);
    }

    public function is_system_detected_as_robot(): bool
    {
        return false;
    }
}
