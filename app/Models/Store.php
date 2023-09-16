<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory ;

    protected $guarded=['id'];

    protected $casts=[
        'status'=>StatusEnum::class,
        'price'=>Money::class,
        'notify_price'=>Money::class,
        'pivot.updated_at'=>'datetime',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps()->withPivot([
            'id',
            'price',
            'notify_price',
            'rate',
            'number_of_rates',
            'seller',
            'coupons',
            'shipping_price',
            'special_offers',
            'in_stock',
            'add_shipping',
            'updated_at',
            'ebay_id'
        ]);
    }
    }
