<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order;
use App\Trait\CustomRespone;

use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    use CustomRespone;

    public function index($warehouse)
    {
        $order = Order::where('id', $warehouse)->orWhere('warehouse_number', $warehouse)
            ->with(
                'trackings',
                (function ($query) {
                    $query->orderBy('created_at', 'desc');
                })
            )
            ->first();
        if (!$order) {
            return $this->json(404, false, 'Order not found');
        }
        return $this->json(200, true, 'Order trackings retrieved successfully.', new OrderResource($order));
    }
}
