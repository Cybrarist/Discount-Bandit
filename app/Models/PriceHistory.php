<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $guarded=['id'];


    public function products()
    {
        return $this->belongsTo(Product::class)->withTimestamps();
    }
    public function services()
    {
        return $this->belongsTo(Service::class)->withTimestamps();
    }
}
