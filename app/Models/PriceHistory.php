<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;


    protected $fillable=[
        "date",
        "price",
        "product_id",
        "store_id",
        "used_price",
    ];

    protected function casts(): array
    {
        return [
            "price"=>Money::class,
            "used_price"=>Money::class
        ];
    }

    public function product_store()
    {
        return $this->belongsTo(ProductStore::class);
    }
}
