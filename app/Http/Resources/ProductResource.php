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
                'description' => $this->description, 
                'is_active' => (bool) $this->is_active,
                'created_date' => $this->created_at->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'manzil_choice'=> (bool) $this->manzil_choice,
                'sponsor'=> (bool) $this->sponsor,
                'product_variants' => ProductVariantResource::collection($this->productVariants),
                'category' => new CategoryResource($this->category), 
                'sub_category' => new SubCategoryResource($this->subCategory),
                'reseller' => new ResellerResource($this->whenLoaded('reseller')),
         
                'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
                'created_at' => $this->created_at->diffForHumans(), // Human-readable format
                'updated_at' => $this->updated_at->diffForHumans(),
            ];
    }
}
