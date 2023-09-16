<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    protected $casts=[
        'status'=>StatusEnum::class,
        'stores.pivot.price'=>Money::class,
        'stores.pivot.notify_price'=>Money::class,
        'stores.pivot.updated_at'=>'datetime',
    ];



    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }


    public function stores()
    {
        return $this->belongsToMany(Store::class)->withTimestamps()->withPivot([
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
            'ebay_id',
        ]);
    }

    public function variations()
    {
        return $this->hasMany(Product::class);
    }


    public function parent_variation()
    {
        return $this->belongsTo(Product::class , 'id' , 'product_id');
    }


}
