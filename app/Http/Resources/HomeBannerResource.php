<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id'    => $this->id,
            'title' => $this->title,     
            'description' => $this->description,
            'button_text' => $this->button_text, 
            'button_link' => $this->button_link, 
            'is_active' => $this->is_active, 

            'image_url' => asset('storage/' . $this->image_url),
            'product'=>[
                'id' => $this->product->id,
                'name' => $this->product->name,
            ]
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_for_humans' => $this->created_at->diffForHumans(),
            'updated_at_for_humans' => $this->updated_at->diffForHumans(),
        ];
    }
}
