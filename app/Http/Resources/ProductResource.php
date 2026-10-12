<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'name' => $this->whenHas('name'),
            'image' => $this->whenHas('image'),
            'status' => $this->whenHas('status'),
            'is_favourite' => $this->whenHas('is_favourite'),
            'lowest_price' => $this->whenHas('lowest_price'),
            'highest_price' => $this->whenHas('highest_price'),
            'snoozed_until' => $this->whenHas('snoozed_until'),
            'max_notifications_daily' => $this->whenHas('max_notifications_daily'),
            'notifications_sent' => $this->whenHas('notifications_sent'),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
            'links' => $this->whenLoaded('links'),
            'categories' => $this->whenLoaded('categories'),
        ];
    }
}
