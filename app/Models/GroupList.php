<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupList extends Model
{
    use HasFactory;

    protected $guarded=['id'];



    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('qty')->withTimestamps();
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

}
