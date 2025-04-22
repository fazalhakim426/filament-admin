<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Trait\CustomRespone;

class ReviewController extends Controller
{
    use CustomRespone;

    public function index()
    {
        $query = Review::whereHas('product', function ($q) {
            $q->where('supplier_user_id', Auth::id());
        });
    
        // Optional filters
        if (request()->filled('rating')) {
            $query->where('rating_stars', request('rating'));
        }
    
        if (request()->filled('product_id')) {
            $query->where('product_id', request('product_id'));
        }
    
        if (request()->filled('user_id')) {
            $query->where('user_id', request('user_id'));
        }
    
        if (request()->filled('from_date')) {
            $query->whereDate('created_at', '>=', request('from_date'));
        }
    
        if (request()->filled('to_date')) {
            $query->whereDate('created_at', '<=', request('to_date'));
        }
    
        $reviews = $query->with('product', 'user')->paginate(request('per_page', 15));
    
        return $this->json(200, true, 'Reviews retrieved successfully.', [
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ],
            'links' => [
                'first' => $reviews->url(1),
                'last' => $reviews->url($reviews->lastPage()),
                'prev' => $reviews->previousPageUrl(),
                'next' => $reviews->nextPageUrl(),
            ]
        ]);
    }
    
}
