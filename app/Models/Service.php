<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $casts=['status'=>StatusEnum::class];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps()->withPivot([
            'price',
            'notify_price',
            'rate',
            'number_of_rates',
            'seller',
            'shipping_price',
            'special_offers',
            'is_prime',
            'in_stock'
        ]);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function groupLists()
    {
        return $this->belongsToMany(GroupList::class , 'group_list_services')->withPivot('notify_price')->withTimestamps();
    }

    public function price_histories()
    {
        return $this->belongsToMany(PriceHistory::class);
    }

}
