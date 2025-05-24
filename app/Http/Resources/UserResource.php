<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if($request->user()) { 
            $user = $request->user();
            $followed = $user->followedSuppliers()->where('supplier_user_id', $this->id)->exists();
            count($user->followedSuppliers()->where('supplier_user_id', $this->id)->get());
            $countFollowedSuppliers = $user->followedSuppliers()->where('supplier_user_id', $this->id)->count();
        } else { 

            $followed = false;
            $countFollowedSuppliers = 0;
        } 
        $data = [
            'id' => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "active" => $this->active ? true : false,
            'count_followed'=> $countFollowedSuppliers,
            "followed" =>  $followed, 
            "profile_photo_path" => $this->profile_photo_path,
            "address" => $this->address,
            "roles" => $this->roles->pluck('name'),
            "phone" => $this->phone,
            "whatsapp" => $this->whatsapp,
            "referral_code" => $this->referral_code,
            "balance" => $this->balance,
            "city" => new CityResource($this->city),
            "state" =>  $this->state,
            "country" => new CountryResource($this->country),
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_for_humans' => $this->created_at->diffForHumans(),
            'updated_at_for_humans' => $this->updated_at->diffForHumans(),
        ];

        if (in_array('Supplier', $this->roles->pluck('name')->toArray())||$this->supplierDetail) {
            $data['supplier_detail'] = new SupplierDetailResource($this->supplierDetail);
        }
        return $data;
    }
}
