<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierProductResource extends JsonResource
{
    public function toArray(Request $request): array
    { 
        
        $averageRating = $this->reviews()->avg('rating_stars');
        $totalSold = $this->orderItems()
            ->whereHas('order', fn($query) => $query->where('order_status', '!=', 'canceled'))
            ->sum('quantity');  
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'manzil_choice' => (bool) $this->manzil_choice,
            'sponsor' => (bool) $this->sponsor,
            'product_variants' => ProductVariantResource::collection($this->whenLoaded('productVariants')),
            'category' => new CategoryResource($this->category),
            'sub_category' => new SubCategoryResource($this->subCategory),
            'reseller' => new ResellerResource($this->whenLoaded('reseller')),
            'supplier' => new UserResource($this->whenLoaded('supplierUser')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'average_rating' => round($averageRating),
            'total_sold' => (int) $totalSold,
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_for_humans' => $this->created_at->diffForHumans(),
            'updated_at_for_humans' => $this->updated_at->diffForHumans(),
        ];
    }
}
