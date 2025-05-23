<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
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
            'quantity' => $this->quantity,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'unit_price' => $this->unit_price,
            'description' => $this->description,
            'type'=>    $this->type, 
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_for_humans' => $this->created_at->diffForHumans(),
            'updated_at_for_humans' => $this->updated_at->diffForHumans(),
            'product' => new ProductResource($this->whenLoaded('product')),
            'supplier' => new UserResource($this->whenLoaded('supplierUser')),
        ];
    }
}
