<?php

namespace App\Services;

use Dom\HTMLDocument;
use Illuminate\Support\Str;

class SchemaParser
{
    public private(set) array $schema = [];

    public function __construct(
        public HTMLDocument $dom
    ) {
        $this->parse_script_tags_with_product_group_type_schema();
        $this->parse_script_tags_with_product_type_schema();
        $this->parse_html_product_schema();

    }

    private function parse_script_tags_with_product_group_type_schema(): void
    {
        $scripts = $this->dom->querySelectorAll('script[type="application/ld+json"]');

        foreach ($scripts as $script) {
            if (Str::contains(Str::remove(" ", $script->textContent), '"@type":"ProductGroup"', true)) {
                $this->schema['product_group'] = json_decode($script->textContent, true);
            }
        }
    }

    private function parse_script_tags_with_product_type_schema(): void
    {
        // todo remove ld+json part or check if all of them must have it
        $scripts = $this->dom->querySelectorAll('script[type="application/ld+json"], script');

        foreach ($scripts as $script) {
            if (
                Str::contains(Str::remove(" ", $script->textContent), '"@type":"Product"', true) &&
                ! Str::contains(Str::remove(" ", $script->textContent), '"@type":"ProductGroup"', true)
            ) {
                $this->schema['product'] = json_decode($script->textContent, true);
                if ($this->schema['product'] != null) {
                    break;
                }
            }
        }
    }

    private function parse_html_product_schema(): void
    {
        if (count($this->schema) > 0) {
            return;
        }

        $direct_schema = [
            'name',
            'price',
            'priceCurrency',
            'availability',
        ];

        $product_schema = $this->dom->querySelectorAll('[itemType$="schema.org/Product"]');

        if (! count($product_schema)) {
            return;
        }

        foreach ($direct_schema as $field) {
            $element = $product_schema[0]?->querySelector("meta[itemprop=\"{$field}\"]");
            $this->schema['product'][$field] = $element ? $element->getAttribute('content') : null;
        }

        $image_attribtues = [
            'content',
            'href',
            'src',
        ];

        $image_query = $product_schema[0]?->querySelectorAll('[itemprop="image"]');

        foreach ($image_attribtues as $attribute) {
            $image_url = $image_query[0]?->getAttribute($attribute);
            if (! empty($image_url)) {
                $this->schema['product']['image'] = $image_url;
                break;
            }
        }

        $seller_structre = [
            'meta[itemprop="seller"]',
            'meta[itemprop="seller"] meta[itemprop="name"]',
        ];

        foreach ($seller_structre as $seller_element) {
            $this->schema['product']['seller'] = $product_schema[0]->querySelector($seller_element)?->getAttribute('content');
            if (! empty($this->schema['product']['seller'])) {
                break;
            }
        }

        $this->schema['product']['aggregateRating']['reviewCount'] = $product_schema[0]->querySelector('meta[itemprop="reviewCount"]')?->getAttribute('content');
        $this->schema['product']['aggregateRating']['ratingValue'] = $product_schema[0]->querySelector('meta[itemprop="ratingValue"]')?->getAttribute('content');

    }
}
