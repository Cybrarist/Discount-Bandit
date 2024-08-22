<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use App\Observers\StoreObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[ObservedBy(StoreObserver::class)]
class Store extends Model
{
    use HasFactory ;

    protected $fillable=[
        "deleted_at",
        "name",
        "domain",
        "image",
        "slug",
        "status",
        "tabs",
        "currency_id",
    ];


    protected function casts(): array
    {
        return [
            'status'=>StatusEnum::class,
            'price'=>Money::class,
            'lowest_price'=>Money::class,
            'highest_price'=>Money::class,
            'notify_price'=>Money::class,
            'shipping_price'=>Money::class,
            'pivot.updated_at'=>'datetime',
        ];
    }


    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps()->withPivot([
            "id",
            //data
            'price',
            'notify_price',
            'rate',
            'number_of_rates',
            'seller',
            'shipping_price',
            'updated_at',
            //extra settings
            'add_shipping',
            //ebay
            'remove_if_sold',
            'ebay_id',
        ]);
    }

    public function product_stores(): HasMany
    {
        return $this->hasMany(ProductStore::class);
    }
}
