<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "description"=>$this->description,
            'url'=> asset('storage/'.$this->image),
            'banners' => ImageResource::collection($this->images),
            "sub_categories" => SubCategoryResource::collection($this->whenLoaded('subCategory')),
            
        ];
    }
}
