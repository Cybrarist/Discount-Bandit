<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductService extends Pivot
{
    protected $casts=[
        'coupons'=>"array"
    ];
}
