<?php

namespace App\Http\Controllers\Api\Common;

use App\Models\Product;
use App\Http\Controllers\Controller; 
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Trait\CustomRespone;  
class ProductReviewController extends Controller
{
    use CustomRespone;
    public function reviews(Product $product)
    {
        $params = request()->only(['per_page']);
        $query = Review::where('product_id', $product->id)->with('user'); 
        $perPage = $params['per_page'] ?? 10;
        return response()->json([
            'success' => true,
            'message' => 'Product reviews lists',
            'data' => ReviewResource::collection($query->paginate($perPage))->response()->getData(true)
        ]);
    }
}