<?php

namespace App\Http\Resources;

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
        return
            [
                'id' => $this->id,
                'name' => $this->name,
                'sku' => $this->sku,
                'is_active' => (bool) $this->is_active,
                'description' => $this->description,
                'stock_quantity' => $this->stock_quantity,
                'unit_selling_price' => $this->unit_selling_price,
                'referral_reward_type' => $this->referral_reward_type,
                'referral_reward_value' => $this->referral_reward_value,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'category' => new CategoryResource($this->category),
                'reseller' => new ResellerResource($this->whenLoaded('reseller')),
                'created_at' => $this->created_at->diffForHumans(), // Human-readable format
                'updated_at' => $this->updated_at->diffForHumans(),
            ];
    }
}
