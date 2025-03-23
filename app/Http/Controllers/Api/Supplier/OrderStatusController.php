<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller; 
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order; 
use App\Trait\CustomRespone; 

use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    use CustomRespone;

    private function updateStatus(Request $request, Order $order, string $status): JsonResponse
    {
        if ($order->order_status == $status) {
            return $this->json(200, true, 'order already ' . $status);
        }

        $allowedStatuses = [
            'new',
            'accepted',
            'processing',
            'confirmed',
            'ready-to-dispatched',
            'shipped',
            'paid',
            'refunded',
            'dispatched',
            'intransit',
            'delivered',
            'returned',
            'canceled'
        ];


        if (!in_array($status, $allowedStatuses)) {
            return $this->json(422, false, 'Invalid status');
        }

        $order->update(['order_status' => $status]);


        return  $this->json(200, true, "Order marked as $status");
    }

    function accepted(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'accepted');
    }


    public function processing(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'processing');
    }

    public function rejected(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'rejected');
    }

    public function readyToDispatched(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'ready-to-dispatched');
    }

    public function dispatched(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'dispatched');
    }

    public function intransit(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'intransit');
    }

    public function delivered(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'delivered');
    }

    public function returned(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'returned');
    }

    public function canceled(Request $request, Order $order)
    {
        return $this->updateStatus($request, $order, 'canceled');
    }
 
}
