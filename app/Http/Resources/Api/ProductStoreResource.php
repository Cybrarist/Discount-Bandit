<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "store"=> new StoreResource($this->store),
            "price" => $this->price,
            "used_price" => $this->used_price,
            "notify_price" => $this->notify_price,
            "notify_percentage"=> $this->notify_percentage,
            "rate"=> $this->rate,
            "number_of_rates"=> $this->number_of_rates,
            "seller"=> $this->seller,
            "shipping_price"=> $this->shipping_price,
            "condition"=> $this->condition,
            "notifications_sent"=> $this->notifications_sent,
            "add_shipping"=> $this->add_shipping,
            "in_stock"=> $this->in_stock,
            "key"=> $this->key,
            "highest_price"=> $this->highest_price,
            "lowest_price"=> $this->lowest_price,
        ];
    }
}
