<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLinkHistory extends Model
{
    /** @use HasFactory<\Database\Factories\ProductLinkHistoryFactory> */
    use HasFactory;
    protected $fillable = [
        'product_link_id',
        'price',
        'date',
        'used_price',
    ];

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'used_price' => MoneyCast::class,
        ];
    }
}
