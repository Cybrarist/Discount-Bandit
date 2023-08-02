<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Jobs\GetProductJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $casts=['status'=>StatusEnum::class];

    protected static function booted()
    {
        static::created(function ($product){
            foreach ($product->services as $service)
                GetProductJob::dispatch($product, $service);
        });
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->using(ProductService::class)->withTimestamps()->withPivot([
            'price',
            'notify_price',
            'rate',
            'number_of_rates',
            'seller',
            'coupons',
            'shipping_price',
            'special_offers',
            'is_prime',
            'in_stock'
        ]);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function price_histories()
    {
        return $this->hasMany(PriceHistory::class);

    }


    public function group_lists()
    {
        return $this->belongsToMany(GroupList::class);
    }





}

