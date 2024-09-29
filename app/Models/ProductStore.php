<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductStore extends Pivot
{

    protected $fillable = [
        "product_id",
        "store_id",
        "price",
        "used_price",
        "notify_price",
        "notify_percentage",
        "rate",
        "number_of_rates",
        "seller",
        "offers",
        "shipping_price",
        "condition",
        "notifications_sent",
        "lowest_30",
        "add_shipping",
        "in_stock",
        "key",
        "highest_price",
        "lowest_price",
    ];


    protected function casts(): array
    {
        return [
            'price'=>Money::class,
            'highest_price'=>Money::class,
            'lowest_price'=>Money::class,
            'used_price'=>Money::class,
            'notify_price'=>Money::class,
            'notify_percentage'=>Money::class,
            'shipping_price'=>Money::class,
        ];
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    public function stores_available(): BelongsTo
    {
        return $this->belongsTo(Store::class , 'store_id')->whereHas('products');
    }

}
