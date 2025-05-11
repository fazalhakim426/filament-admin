<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderMiniResource extends JsonResource
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
                'warehouse_number' => $this->warehouse_number,
                'total_price' => (int)$this->total_price,
                'shipping_cost' => (int)$this->shipping_cost,
                'commission_cost' => (int)$this->commission_cost,
                'items_cost' => (int)$this->items_cost,
                'order_status' => $this->order_status,
                'payment_status' => $this->payment_status,
                
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_for_humans' => $this->created_at->diffForHumans(),
            'updated_at_for_humans' => $this->updated_at->diffForHumans(),
            ];
    }
}
