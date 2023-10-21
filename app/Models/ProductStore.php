<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductStore extends Pivot
{
    //

    protected $casts=[
        'price'=>Money::class,
        'notify_price'=>Money::class,
        'shipping_price'=>Money::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stores_available()
    {
        return $this->belongsTo(Store::class , 'store_id')->whereHas('products');
    }
}
