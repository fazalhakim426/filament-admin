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
            'unit_cost_price' => $this->unit_cost_price,
            'description' => $this->description,
            'type'=>    $this->type,
            'product' => new ProductResource($this->whenLoaded('product')),
            'supplier' => new UserResource($this->supplierUser),
        ];
    }
}
