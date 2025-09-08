<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class RssFeedItem extends Model implements Feedable
{
    /** @use HasFactory<\Database\Factories\RssFeedItemFactory> */
    use HasFactory;

    protected $fillable = [
        "data",
        "user_id",
    ];

    protected function casts(): array
    {
        return [
            'data' => 'json',
        ];
    }

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create([
            "id" => $this->id,
            'title' => $this->data["title"],
            'summary' => $this->data["summary"],
            'updated' => now(),
            'link' => $this->data['link'],
            'image' => $this->data['image'],
            'authorName' => "Discount Bandit",
            'user_id' => $this->user_id,
        ]);
    }

    public static function getFeedItems()
    {
        $validated = request()->validate([
            'feed_id' => ['string', 'required'],
        ]);

        $user = User::firstWhere('rss_feed', $validated['feed_id']);

        abort_if(!$user, 500);

        return $user->rss_feed_items()
            ->latest()
            ->take(50)
            ->get();
    }
}
