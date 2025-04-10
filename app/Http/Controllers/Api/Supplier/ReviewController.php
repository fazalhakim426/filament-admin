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
        $reviews = Review::whereHas('product', function ($query) {
            $query->where('supplier_user_id', Auth::id());
        })
            ->with('product', 'user')
            ->paginate(request('per_page', 15));

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
