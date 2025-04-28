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
        $params = request()->only(['is_active']);
        $query = HomeBanner::with('product');
        if (isset($params['is_active'])) {
            $isActive = $params['is_active'];
            $query->where('is_active', $isActive == "1" || $isActive == "true" ? 1 : 0);
        } 
        return $this->json(200,true, 'HomeBanner lists',HomeBannerResource::collection($query->latest()->get()) );
    }
    public function show($id)
    {
        $homeBanner = HomeBanner::with('product')->findOrFail($id);
        return $this->json(200,true, 'HomeBanner detail list',new HomeBannerResource($homeBanner)
        );
    }
}
