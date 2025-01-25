<?php

namespace App\Http\Resources;

use App\Http\Resources\SupplierDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->roles->pluck('name'), // Assumes a relationship like $user->roles
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
        if (in_array('Supplier', $this->roles->pluck('name')->toArray())) {
            $data['supplier_detail'] = new SupplierDetailResource($this->supplierDetail);
        }

        return $data;
    }
}
