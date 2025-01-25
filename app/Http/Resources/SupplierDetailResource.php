<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    { 
        return [
            'business_name' => $this->business_name,
            'contact_person' => $this->contact_person,
            'website' => $this->website,
            'supplier_type' => $this->supplier_type,
            'main_category_id' => $this->main_category_id,
            'secondary_category_id' => $this->secondary_category_id,
            'product_available' => $this->product_available,
            'product_source' => $this->product_source,
            'product_unit_quality' => $this->product_unit_quality,
            'self_listing' => $this->self_listing,
            'product_range' => $this->product_range,
            'using_daraz' => $this->using_daraz,
            'daraz_url' => $this->daraz_url,
            'ecommerce_experience' => $this->ecommerce_experience,
            'term_agreed' => $this->term_agreed,
            'marketing_type' => $this->marketing_type,
            'preferred_contact_time' => $this->preferred_contact_time,
        ];
    }
}
