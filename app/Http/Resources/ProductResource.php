<?php

namespace App\Http\Resources;

use App\Models\Review;
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
                'price' =>(int)$this->unit_selling_price,
                'referral_reward_type' => $this->referral_reward_type,
                'referral_reward_value' => (int) $this->referral_reward_value,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'manzil_choice'=>$this->manzil_choice,
                'sponsor'=>$this->sponsor,

                'category' => new CategoryResource($this->category), 
                'sub_category' => new SubCategoryResource($this->subCategory),
                'reseller' => new ResellerResource($this->whenLoaded('reseller')),
         
                'review' => ReviewResource::collection($this->whenLoaded('reviews')),
                'created_at' => $this->created_at->diffForHumans(), // Human-readable format
                'updated_at' => $this->updated_at->diffForHumans(),
            ];
    }
}
