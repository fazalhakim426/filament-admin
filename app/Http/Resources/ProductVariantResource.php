<?php

namespace App\Http\Resources;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'price' => $this->unit_selling_price,
            'discount' => $this->discount,
            'discount_description' => $this->discount_description,
            'stock_quantity' => $this->stock_quantity, 
            'description' => $this->description,
            'images' => ImageResource::collection($this->images),
            'variant_options' => VariantOptionResource::collection($this->variantOptions),
            // 'variants' => VariantKeyValueOptionResource::collection($this->variantOptions),
            'inventory_movements' => InventoryMovementResource::collection($this->whenLoaded('inventoryMovements')),
             
        ];
    }
}
