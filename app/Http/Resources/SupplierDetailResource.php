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
            'website' => $this->website,
            'supplier_type' => $this->supplier_type,
            'category' => new CategoryResource($this->category),
            'sub_category' => new SubCategoryResource($this->subCategory),
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
            'cnic' => $this->cnic,
            'contact_person' => $this->contact_person,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch, 
            'bank_account_number' => $this->bank_account_number,
            'bank_iban' => $this->bank_iban,
            'term_of_services' => $this->term_of_services,

        ];
    }
}
