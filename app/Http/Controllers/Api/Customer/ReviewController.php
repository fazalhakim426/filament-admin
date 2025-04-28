<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Order;
use App\Trait\CustomRespone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pest\ArchPresets\Custom;

class ReviewController extends Controller
{
    use CustomRespone;
 
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id', 
            'rating_stars' => 'required|numeric|min:0|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]); 

        $review = Review::create([
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
            'rating_stars' => $request->rating_stars,
            'review_text' => $request->review_text,
        ]);

        return $this->json(200, true, 'Review submitted successfully.', [
            'review' => new ReviewResource($review),
        ]);
    }
}
