<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class RssFeedItem extends Model implements  Feedable
{
    use HasFactory;

    protected $fillable=[
        "data"
    ];

    protected function casts(): array
    {
        return [
            'data'=>'json'
        ];
    }


    public function toFeedItem(): FeedItem
    {

        return FeedItem::create([
            "id"=>$this->id,
            'title' => $this->data["title"],
            'summary' => $this->data["summary"],
            'updated' => now(),
            'link' => route("filament.admin.resources.product-stores.edit" , $this->data["product_id"]),
            'image' => Product::find($this->data["product_id"])->image,
            'authorName' => "Discount Bandit",
        ]);
    }
    public static function getFeedItems()
    {
        return RssFeedItem::all();
    }

}
