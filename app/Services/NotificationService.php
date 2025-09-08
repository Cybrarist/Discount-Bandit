<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\ProductLink;
use App\Models\ProductLinkHistory;

class NotificationService
{
    public array $notification_reasons = [];

    public function __construct(
        public ProductLink $old_product_link,
        public ProductLink $new_product_link,
        public NotificationSetting $notification_setting,
    ) {}

    public function check(): bool
    {

        if ($this->is_product_snoozed()) {
            return false;
        }

        if ($this->old_product_link->price == $this->new_product_link->price) {
            return false;
        }

        if ($this->notification_setting->is_official && ! $this->new_product_link->is_official) {
            return false;
        }

        $this->notification_reasons[] = 'Official Seller';

        if ($this->notification_setting->any_price_change) {
            $this->notification_reasons[] = "price changed by {$this->old_product_link->price} to {$this->new_product_link->price}";
        }

        if ($this->notification_setting->is_in_stock && $this->new_product_link->is_in_stock) {
            $this->notification_reasons[] = 'in stock';
        }

        $this->is_price_lowest_in_x_days($this->notification_setting->price_lowest_in_x_days);

        if ($this->new_product_link->price <= $this->notification_setting->price_desired) {
            $this->notification_reasons[] = 'price reached desired value';
        }

        $this->is_price_dropped_in_x_percent();


        return true;
    }

    private function is_product_snoozed(): bool
    {
        return $this->old_product_link->product->snoozed_until &&
            $this->old_product_link->product->snoozed_until->isFuture();
    }

    private function is_price_lowest_in_x_days($days): void
    {

        $lowest_price_in_database = ProductLinkHistory::orderBy('price')
            ->whereBetween('created_at', [now()->subDays($days), now()])
            ->where('product_link_id', $this->old_product_link->id)
            ->where('price', '<=', $this->new_product_link->price)
            ->exists();

        if (! $lowest_price_in_database) {
            return;
        }

        $this->notification_reasons[] = "price is lowest since {$this->notification_setting->price_lowest_in_x_days} days";
    }

    private function is_price_dropped_in_x_percent(): void
    {
        if (! $this->notification_setting->percentage_drop) {
            return;
        }

        if ((
            ($this->old_product_link->price - $this->new_product_link->price)
            / $this->old_product_link->price
        )
            * 100 >=
            $this->notification_setting->percentage_drop) {
            $this->notification_reasons[] = 'price dropped in x percent';
        }
    }
}
