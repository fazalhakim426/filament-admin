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
                'price' =>(int) $this->price, 
                'commission' =>(int) $this->commission, 
                'discount' =>(int) $this->discount, 
                'total_value' =>  $this->quantity*$this->price, 
                'product_variant_id'=> $this->product_variant_id,
                'productVariant' => new ItemProductVariantResource($this->whenLoaded('productVariant')),
                // 'product' => new ItemProductResource($this->whenLoaded('product')),
                // 'order' => new  OrderResource($this->order), 
                // 'supplier' => new ProductResource($this->supplierUser),
            ];
    }
}
