<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    use HasFactory;

    protected $fillable=[
        "name",
        "image",
        "status",
        "favourite",
        "stock",
        "snoozed_until",
        "max_notifications",
        "lowest_within",
        "only_official",
        "walmart_ip",
        "argos_id",
    ];
    protected $casts=[
        'status'=>StatusEnum::class,
//        'stores.pivot.price'=>Money::class,
//        'stores.pivot.price'=>Money::class,
//        'stores.pivot.price'=>Money::class,
//        'stores.pivot.notify_price'=>Money::class,
//        'stores.pivot.shipping_price'=>Money::class,
//        'stores.pivot.updated_at'=>'datetime',
    ];



    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }


    public function stores()
    {
        return $this->belongsToMany(Store::class)->withTimestamps()->withPivot([
            "id",
            'price',
            'highest_price',
            'lowest_price',
            'notify_price',
            'rate',
            'number_of_rates',
            'seller',
            'shipping_price',
            'updated_at',
            'add_shipping',
        ]);
    }

    public function product_stores(): HasMany
    {
        return $this->hasMany(ProductStore::class);
    }
}
