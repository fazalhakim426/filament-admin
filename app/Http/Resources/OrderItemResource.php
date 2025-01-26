<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'quantity' => $this->quantity,
            'price' => $this->price,
            'profit' => $this->price,
            'total' => $this->quantity * $this->price,
            'unit_cost_price' => $this->unit_cost_price,
            'unit_selling_price' => $this->unit_selling_price, 
            'status' => $this->status, 
            'product' => new ProductResource($this->whenLoaded('product')),
            // 'order' => new  OrderResource($this->order),
            // 'product' =>new ProductResource($this->product),
            // 'supplier' => new ProductResource($this->supplierUser),
        ];
    }
}
