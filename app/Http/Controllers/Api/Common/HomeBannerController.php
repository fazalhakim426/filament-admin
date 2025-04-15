<?php

namespace App\Http\Controllers\Api\Common;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\HomeBannerResource;
use App\Models\Review;
use App\Models\HomeBanner;
use App\Trait\CustomRespone;

class HomeBannerController extends Controller
{
    use CustomRespone;
    public function index()
    {
        $params = request()->only(['per_page', 'is_active']);
        $query = HomeBanner::with('product');
        if (isset($params['is_active'])) {
            $isActive = $params['is_active'];
            $query->where('is_active', $isActive == "1" || $isActive == "true" ? 1 : 0);
        }
        $perPage = $params['per_page'] ?? 10;
        return response()->json([
            'success' => true,
            'message' => 'HomeBanner lists',
            'data' => HomeBannerResource::collection($query->paginate($perPage))->response()->getData(true)
        ]);
    }
    public function show($id)
    {
        $homeBanner = HomeBanner::with('product')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'HomeBanner detail list',
            'data' => new HomeBannerResource($homeBanner)
        ]);
    }
}
