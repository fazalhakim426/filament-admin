<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $code =  strtolower($this->code);
        return [
            'id' => $this->id,
            'name' => $this->name,  
            'code' => $this->code,
            'image' => "https://flagcdn.com/24x18/$code.png",
        ];
    }
}
