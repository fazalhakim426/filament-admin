<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserMiniResource extends JsonResource
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
            "name" => $this->name,
            "email" => $this->email, 
            "profile_photo_path" => $this->profile_photo_path,
            "address" => $this->address,
            "roles" => $this->roles->pluck('name'), 
            "city" => new CityResource($this->city),
            "state" =>  $this->state,
            "country" => new CountryResource($this->country),
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans()
        ];  
    }
}
