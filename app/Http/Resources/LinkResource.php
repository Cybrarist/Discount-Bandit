<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'key' => $this->whenHas('key'),
            'name' => $this->whenHas('name'),
            'image' => $this->whenHas('image'),
            'price' => $this->whenHas('price'),
            'used_price' => $this->whenHas('used_price'),
            'highest_price' => $this->whenHas('highest_price'),
            'lowest_price' => $this->whenHas('lowest_price'),
            'shipping_price' => $this->whenHas('shipping_price'),
            'rating' => $this->whenHas('rating'),
            'total_reviews' => $this->whenHas('total_reviews'),
            'seller' => $this->whenHas('seller'),
            'condition' => $this->whenHas('condition'),
            'is_official' => $this->whenHas('is_official', fn () => (bool) $this->is_official),
            'is_in_stock' => $this->whenHas('is_in_stock', fn () => (bool) $this->is_in_stock),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
            'store' => $this->whenLoaded('store'),
            'products' => $this->whenLoaded('products'),
            'notification_settings' => $this->whenLoaded('notification_settings'),
            'link_histories' => $this->whenLoaded('link_histories'),
        ];
    }
}
