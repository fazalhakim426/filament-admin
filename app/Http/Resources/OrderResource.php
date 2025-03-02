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
                'order_status' => $this->status,
                'payment_status' => $this->payment_status,
                'need_to_pay' => $this->need_to_pay,
                'items' =>   OrderItemResource::collection($this->items),
                'recipient'=> new AddressResource($this->recipient),
                'sender'=> new AddressResource($this->sender),
                'customer' => new UserResource($this->whenLoaded('customerUser')),
                'deposit'=> new DepositResource($this->whenLoaded('sender')),
                'created_at' => $this->created_at->diffForHumans(),
                'updated_at' => $this->updated_at->diffForHumans(),
            ];
    }
}
