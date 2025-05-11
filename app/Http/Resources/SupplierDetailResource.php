<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class SupplierDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cacheKey = 'average_rating_supplier_' . $this->user_id;

        $averageRating = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return Review::whereHas('product', function ($query) {
                $query->where('supplier_user_id', $this->user_id);
            })->avg('rating_stars');
        });
        return [
            'business_name' => $this->business_name,
            'totol_products' => $this->user->products->count(),
            'average_rating' => round($averageRating),
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
            'city' => $this->city,
            'state' => $this->state,
            'country' => new CountryResource($this->country),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_for_humans' => $this->created_at->diffForHumans(),
            'updated_at_for_humans' => $this->updated_at->diffForHumans(),
        ];
    }
}
