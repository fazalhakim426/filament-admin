<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    // List all orders
    public function index($paid = null)
    { 
        $user = Auth::user();
        $orderItems = $user->supplier_order_items()->whereHas('order', function ($query) use ($paid) {
            if ($paid === 'paid') {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('deposits')
                        ->whereColumn('orders.id', 'deposits.order_id')
                        ->whereNull('deposits.deleted_at')
                        ->selectRaw('SUM(amount) as total_deposited')
                        ->groupBy('deposits.order_id')
                        ->havingRaw('total_deposited >= orders.total_price');
                });
            }
        })->get();
        

        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'data' => OrderItemResource::collection($orderItems),
        ]);
    }

    // Show a specific order
    public function show(Order $order)
    {
        $order->load('orderItems.product');

        return response()->json([
            'message' => 'Order details retrieved successfully.',
            'data' => $order,
        ]);
    }

    // Confirm an order
    public function confirm(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be confirmed.',
            ], 400);
        }

        $order->update(['status' => 'confirmed']);

        return response()->json([
            'message' => 'Order confirmed successfully.',
        ]);
    }

    // Reject an order
    public function reject(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be rejected.',
            ], 400);
        }

        $order->update(['status' => 'canceled']);

        return response()->json([
            'message' => 'Order rejected successfully.',
        ]);
    }

    // Dispatch an order
    public function dispatch(Order $order)
    {
        if ($order->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed orders can be dispatched.',
            ], 400);
        }

        $order->update(['status' => 'shipped']);

        return response()->json([
            'message' => 'Order dispatched successfully.',
        ]);
    }

    // Generate and download airway bill
    public function airwayBill(Order $order)
    {
        if ($order->status !== 'confirmed') {
            return response()->json([
                'message' => 'Airway bill can only be generated for confirmed orders.',
            ], 400);
        }

        // Generate Airway Bill (placeholder logic)
        $airwayBillContent = "Airway Bill for Order #{$order->id}\nTotal Price: {$order->total_price}";

        $filePath = "airway_bills/order_{$order->id}.txt";
        Storage::disk('local')->put($filePath, $airwayBillContent);

        return response()->download(storage_path("app/{$filePath}"), "Order_{$order->id}_Airway_Bill.txt");
    }
}
