<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            "name"=>$this->name,
            "image"=>$this->image,
            "stores"=>StoreResource::collection($this->stores) ?? null,
            "product_stores"=>ProductStoreResource::collection($this->product_stores)
        ];
    }
}
