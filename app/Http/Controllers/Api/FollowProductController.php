<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FollowProductResource;
use App\Trait\CustomRespone;

class FollowProductController extends Controller
{
    use CustomRespone;
    public function follow(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $request->user()->followedProducts()->syncWithoutDetaching([$product->id]);
        $products = $request->user()->followedProducts()->get();
        return $this->json(200, true, 'Following product list',FollowProductResource::collection($products));
   
    }

    public function unfollow(Request $request, $productId)
    {
        $product = Product::findOrFail($productId); 
        $request->user()->followedProducts()->detach($product->id);
        $products = $request->user()->followedProducts()->get();
        return $this->json(200, true, 'Following product list',FollowProductResource::collection($products));
   
    }

    public function followedProducts(Request $request)
    {
        $products = $request->user()->followedProducts()->get();
        return $this->json(200, true, 'Following product list',FollowProductResource::collection($products));
    }
}
