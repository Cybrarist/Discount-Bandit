<?php

namespace App\Services;

use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\NotificationSetting;
use App\Models\Product;
use App\Models\User;

class NotificationService
{
    public array $notification_reasons = [];

    public $new_price;

    public $old_price;

    public $new_used_price;

    public $current_rate;

    public function __construct(
        public Link $old_link,
        public Link $new_link,
        public NotificationSetting $notification_setting,
        public Product $product
    ) {

        $user = User::with('currency')
            ->findOrFail($this->notification_setting->user_id);

        $this->old_link->loadMissing(['store', 'store.currency']);

        $this->current_rate = ($user->currency) ? $user->currency->rate / $this->old_link->store->currency->rate : 1;

        $this->new_price = $this->new_link->price * $this->current_rate;
        $this->old_price = $this->old_link->price * $this->current_rate;
        $this->new_used_price = $this->new_link->used_price * $this->current_rate;

    }

    public function check(): bool
    {

        // ignore currency rate.

        if (
            ! $this->new_link->price && ! $this->new_link->used_price ||
            $this->is_product_snoozed() ||
            $this->old_link->price == $this->new_link->price ||
            $this->notification_setting->is_official && ! $this->new_link->is_official
        )
            return false;

        $this->notification_reasons[] = ($this->new_link->is_official) ? 'Official Seller' : "Non Official Seller";

        // use currency converted price
        if ($this->notification_setting->any_price_change) {
            $this->notification_reasons[] = "price changed";
        }

        if ($this->notification_setting->is_in_stock && $this->new_link->is_in_stock) {
            $this->notification_reasons[] = 'in stock';
        }

        $this->is_price_lowest_in_x_days();

        $total_price_with_cost = ($this->new_price + $this->notification_setting->extra_costs_amount) + ($this->new_price * $this->notification_setting->extra_costs_percentage / 100);
        // use currency converted price
        if ($total_price_with_cost <= $this->notification_setting->price_desired) {
            $this->notification_reasons[] = "price reached desired value ({$this->notification_setting->price_desired}) {$this->old_link->store->currency->code}";
        }

        $this->is_price_dropped_in_x_percent();

        return count($this->notification_reasons) > 1;
    }

    private function is_product_snoozed(): bool
    {
        return $this->product->snoozed_until &&
            $this->product->snoozed_until->isFuture();
    }

    private function is_price_lowest_in_x_days(): void
    {
        if (! $this->notification_setting->price_lowest_in_x_days) {
            return;
        }

        // check if the price is lowest in today, if yes then don't notify
        $alreadyHasPriceToday = LinkHistory::where('link_id', $this->old_link->id)
            ->where([
                'date' => today(),
                'price' => $this->new_link->price * 1000,
            ])
            ->exists();

        if ($alreadyHasPriceToday)  return;

        // ignore currency rate.
        $lowest_price_in_database = LinkHistory::whereBetween('date', [today()->subDays($this->notification_setting->price_lowest_in_x_days), today()])
            ->where('link_id', $this->old_link->id)
            ->min("price");

        if ($this->new_link->price * 1000 > $lowest_price_in_database) {
            return;
        }

        $this->notification_reasons[] = "price is lowest since {$this->notification_setting->price_lowest_in_x_days} days";
    }

    private function is_price_dropped_in_x_percent(): void
    {
        if (! $this->notification_setting->percentage_drop || $this->old_link->price == 0) {
            return;
        }

        if ((
            ($this->old_link->price - $this->new_link->price)
            / $this->old_link->price
        )
            * 100 >=
            $this->notification_setting->percentage_drop) {
            $this->notification_reasons[] = "price dropped {$this->notification_setting->percentage_drop}%";
        }
    }
}
