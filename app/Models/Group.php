<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
        "notify_price" => Money::class,
        "status" => StatusEnum::class,
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps()->withPivot("key");
    }
}
