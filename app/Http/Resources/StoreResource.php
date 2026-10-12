<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'name' => $this->whenHas('name'),
            'domain' => $this->whenHas('domain'),
            'image' => $this->whenHas('image'),
            'slug' => $this->whenHas('slug'),
            'referral' => $this->whenHas('referral'),
            'status' => $this->whenHas('status'),
            'are_params_allowed' => $this->whenHas('are_params_allowed'),
            'allowed_params' => $this->whenHas('allowed_params'),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
            'currency' => $this->whenLoaded('currency'),
            'links' => $this->whenLoaded('links'),
        ];
    }
}
