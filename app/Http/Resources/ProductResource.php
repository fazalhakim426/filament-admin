<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Load variants and options
        $variants = $this->whenLoaded('productVariants');

        $allOptions = $variants
            ? $variants->flatMap(fn($variant) => $variant->variantOptions->map(fn($opt) => [
                'variant_id' => $variant->id,
                'attribute_name' => strtolower($opt->attribute_name),
                'attribute_value' => $opt->attribute_value,
            ]))
            : collect();

        // Get all sizes
        $sizes = $allOptions
            ->whereIn('attribute_name', ['size', 'Size'])
            ->pluck('attribute_value')
            ->unique()
            ->values();

        // Get all colors
        $colors = $allOptions
            ->whereIn('attribute_name', ['color', 'Color'])
            ->pluck('attribute_value')
            ->unique()
            ->values();

        // Build size => [colors] map
        $sizeColorCombinations = [];

        foreach ($variants as $variant) {
            $size = $variant->variantOptions->whereIn('attribute_name', ['size', 'Size'])->first()?->attribute_value;
            $color = $variant->variantOptions->whereIn('attribute_name', ['color', 'Color'])->first()?->attribute_value;

            if ($size && $color) {
                $sizeColorCombinations[$size][] = $color;
            }
        }

        // Make sure colors are unique per size
        foreach ($sizeColorCombinations as $size => $colorList) {
            $sizeColorCombinations[$size] = array_values(array_unique($colorList));
        }


        $averageRating = $this->reviews()->avg('rating_stars');

        $totalSold = $this->orderItems()
        ->whereHas('order', function($query) {
            $query->where('order_status', '!=', 'canceled');
        })
        ->sum('quantity');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'created_date' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'manzil_choice' => (bool) $this->manzil_choice,
            'sponsor' => (bool) $this->sponsor,
            'available_sizes' => $sizes,
            'available_colors' => $colors,

            'total_sold' => (int) $totalSold,
            // 'sku' => $this->variants->first()?->sku,
            // 'price' => $this->variants->first()?->unit_selling_price,
            // 'stock_quantity' => $this->variants->first()?->stock_quantity, 
            // 'description' => $this->variants->first()?->description,
            // 'images' => ImageResource::collection($this->variants->first()?->images), 
            
            'size_color_combinations' => $sizeColorCombinations,
            'variant_mapping' => $variants->map(function ($variant) {
                    $size = $variant->variantOptions->whereIn('attribute_name', ['size', 'Size'])->first()?->attribute_value;
                    $color = $variant->variantOptions->whereIn('attribute_name', ['color', 'Color'])->first()?->attribute_value;

                    return [
                        'variant_id' => $variant->id,
                        'size' => $size,
                        'color' => $color,
                        'stock_quantity' => $variant->stock_quantity,
                        'in_stock' => $variant->stock_quantity > 0,
                        'price' => $variant->unit_selling_price, // optional: include price per variant
                    ];
                })->filter(fn($v) => $v['size'] && $v['color'])->values(),


            'product_variants' => ProductVariantResource::collection($variants), 
            // 'specifications' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'category' => new CategoryResource($this->category),
            'sub_category' => new SubCategoryResource($this->subCategory),
            'reseller' => new ResellerResource($this->whenLoaded('reseller')),
            'supplier' => new UserResource($this->whenLoaded('supplierUser')), 
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'average_rating' => round($averageRating),
            'created_at' => $this->created_at->diffForHumans(), // Human-readable format
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
