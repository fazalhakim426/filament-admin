<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserMiniResource;
use App\Trait\CustomRespone;

class FollowSupplierController extends Controller
{
    use CustomRespone;
    public function follow(Request $request, $supplierId)
    {
        $supplier = User::findOrFail($supplierId);

        if ($supplier->id === $request->user()->id) {
            return $this->json(400, false, 'You cannot follow yourself');
        }

        $request->user()->followedSuppliers()->syncWithoutDetaching([$supplier->id]);

        return $this->json(200, true, 'followed successfully');
    }

    public function unfollow(Request $request, $supplierId)
    {
        $supplier = User::findOrFail($supplierId);

        $request->user()->followedSuppliers()->detach($supplier->id);

        return $this->json(200, true, 'unfollowed successfully');
    }

    public function followedSuppliers(Request $request)
    {
        $suppliers = $request->user()->followedSuppliers()->get();
        return $this->json(200, true, 'Followings list',UserMiniResource::collection($suppliers));
    }
}
