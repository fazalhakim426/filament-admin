<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource  extends JsonResource
{
    public function toArray(Request $request): array
    {
        
        return [
            'id' => $this->id,
            'rating' => $this->rating_stars,
            'review_text' => $this->review_text,
            'user' => new UserResource($this->whenLoaded('user')), 
        ];
    }
}
