<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemProductVariantResource extends JsonResource
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
            'sku' => $this->sku,
            'description' => $this->description,
            'product' => new ItemProductResource($this->whenLoaded('product')),
            'variantOptions' => VariantOptionResource::collection($this->whenLoaded('variantOptions')),
            'images' => ImageResource::collection($this->images),
        ];
    }
}
