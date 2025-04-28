<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
                'total_price_detail' => [
                    'items_cost'    =>   $this->items_cost,
                    'shipping_cost' => $this->shipping_cost,
                    'items_discount' => $this->items_discount,
                    'items_commission' => $this->items_commission,
                    'items_discount' => $this->items_discount,
                ],
                'payment_status' => $this->payment_status,
                'need_to_pay' => $this->need_to_pay,
                'order_date' => $this->created_at->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at->diffForHumans(),
                'updated_at' => $this->updated_at->diffForHumans(),
                'trackings' => OrderTrackingResource::collection($this->whenLoaded('trackings')),
                'items' =>   OrderItemResource::collection($this->items),
                'recipient' => new AddressResource($this->recipient),
                'sender' => new AddressResource($this->sender),
                'customer' => new UserResource($this->whenLoaded('customerUser')),
                'deposit' => new DepositResource($this->whenLoaded('deposits')),
            ];
    }
}
