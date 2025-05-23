<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variants = $this->whenLoaded('productVariants');

        $allOptions = $variants
            ? $variants->flatMap(fn($variant) => $variant->variantOptions->map(fn($opt) => [
                'variant_id' => $variant->id,
                'attribute_name' => strtolower($opt->attribute_name),
                'attribute_value' => $opt->attribute_value,
            ]))
            : collect();
 
        $availableAttributes = $allOptions
            ->groupBy('attribute_name')
            ->map(fn($group) => $group->pluck('attribute_value')->unique()->values());
 
        $variantMapping = $variants ? $variants->map(function ($variant) {
            $attributes = $variant->variantOptions->mapWithKeys(function ($opt) {
                return [strtolower($opt->attribute_name) => $opt->attribute_value];
            });

            return [
                'variant_id' => $variant->id,
                'attributes' => $attributes,
                'stock_quantity' => $variant->stock_quantity,
                'in_stock' => $variant->stock_quantity > 0,
                'price' => $variant->unit_selling_price,
            ];
        })->values() : collect();

        $averageRating = $this->reviews()->avg('rating_stars');
        $totalSold = $this->orderItems()
            ->whereHas('order', fn($query) => $query->where('order_status', '!=', 'canceled'))
            ->sum('quantity'); 
        $attributeStockStatus = [];

        foreach ($variantMapping as $variant) {
            foreach ($variant['attributes'] as $attributeName => $attributeValue) {
                if (!isset($attributeStockStatus[$attributeName])) {
                    $attributeStockStatus[$attributeName] = [];
                }

                if (!isset($attributeStockStatus[$attributeName][$attributeValue])) {
                    $attributeStockStatus[$attributeName][$attributeValue] = [
                        'in_stock' => false,
                        'total_stock' => 0,
                    ];
                }
 
                $attributeStockStatus[$attributeName][$attributeValue]['total_stock'] += $variant['stock_quantity'];

                if ($variant['stock_quantity'] > 0) {
                    $attributeStockStatus[$attributeName][$attributeValue]['in_stock'] = true;
                }
            }
        }

       

        $attributeStockMap = [];

        foreach ($variantMapping as $variant) {
            $attributes = $variant['attributes']->toArray(); // <- FIXED
        
            $attributeKeys = array_keys($attributes);
        
            for ($i = 0; $i < count($attributeKeys) - 1; $i++) {
                $parentKey = $attributeKeys[$i];
                $parentValue = $attributes[$parentKey];
        
                $childKey = $attributeKeys[$i + 1];
                $childValue = $attributes[$childKey];
        
                if (!isset($attributeStockMap[$parentKey])) {
                    $attributeStockMap[$parentKey] = [];
                }
        
                if (!isset($attributeStockMap[$parentKey][$parentValue])) {
                    $attributeStockMap[$parentKey][$parentValue] = [];
                }
        
                if (!isset($attributeStockMap[$parentKey][$parentValue][$childKey])) {
                    $attributeStockMap[$parentKey][$parentValue][$childKey] = [];
                }
        
                $attributeStockMap[$parentKey][$parentValue][$childKey][$childValue] = [
                    'in_stock' => $variant['in_stock'],
                    'stock_quantity' => $variant['stock_quantity'],
                ];
            }
        }
        
       if($request->user()) { 
            $user = $request->user();
            $favorite = $user->followedProducts()->where('product_id', $this->id)->exists(); 
            $countFavorite = $user->followedProducts()->where('product_id', $this->id)->count();
        } else {
            $favorite = false;
            $countFavorite = 0;
        } 
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'manzil_choice' => (bool) $this->manzil_choice,
            'supplier_user_id'=>$this->supplier_user_id, 
            'count_favorite'=> $countFavorite,
            "favorite" =>  $favorite, 
            'sponsor' => (bool) $this->sponsor,
            'available_attributes' => $availableAttributes,
            'variant_mapping' => $variantMapping,
            'attribute_stock_map' => $attributeStockMap,

            'product_variants' => ProductVariantResource::collection($variants),
            'category' => new CategoryResource($this->category),
            'sub_category' => new SubCategoryResource($this->subCategory),
            'reseller' => new ResellerResource($this->whenLoaded('reseller')),
            'supplier' => new UserResource($this->whenLoaded('supplierUser')),
            'average_rating' => round($averageRating),
            'total_sold' => (int) $totalSold,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_for_humans' => $this->created_at->diffForHumans(),
            'updated_at_for_humans' => $this->updated_at->diffForHumans(),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'review_analytics'=> [
                    'total_reviews' => $this->reviews()->count(),
                    'average_rating' => number_format($this->reviews()->avg('rating_stars'),2),
                    'one_star' => $this->reviews()->where('rating_stars', 1)->count(),
                    'two_star' => $this->reviews()->where('rating_stars', 2)->count(),
                    'three_star' => $this->reviews()->where('rating_stars', 3)->count(),
                    'four_star' => $this->reviews()->where('rating_stars', 4)->count(),
                    'five_star' => $this->reviews()->where('rating_stars', 5)->count()
            ]
        ];
    }
}
