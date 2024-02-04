<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $casts=[
        "price"=>Money::class
    ];
    protected $guarded=['id'];

    public function product_store()
    {
        return $this->belongsTo(ProductStore::class);
    }
}
