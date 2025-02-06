<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    // List all orders
    public function index($paid = null)
    {
        $user = Auth::user();

        // Fetch orders as supplier and eager load the related order items
        $orders = $user->ordersAsSupplier()->with(['items' => function ($query) {
            $query->with('product')->where('supplier_user_id', Auth::id());
        }])->get(); // Use get() to execute the query and retrieve the results

        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'data' => OrderResource::collection($orders),
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
    public function confirmOrder(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be confirmed.',
            ], 400);
        }

        $order->update(['order_status' => 'confirmed']);

        return response()->json([
            'message' => 'Order confirmed successfully.',
        ]);
    }

    // Confirm an order
    public function confirmOrderItem($id)
    {
        $item = OrderItem::find($id);
        if ($item->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be confirmed.',
            ], 400);
        }
        $item->update(['order_status' => 'confirmed']);

        return response()->json([
            'message' => 'Order item confirmed successfully.',
        ]);
    }

    // Reject an order
    public function rejectOrder(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be rejected.',
            ], 400);
        }

        $order->update(['order_status' => 'canceled']);

        return response()->json([
            'message' => 'Order rejected successfully.',
        ]);
    }
    // Confirm an order
    public function rejectOrderItem($id)
    {
        $item = OrderItem::find($id);
        if ($item->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be rejected.',
            ], 400);
        }
        $item->update(['order_status' => 'confirmed']);

        return response()->json([
            'message' => 'Order item rejected successfully.',
        ]);
    }

    // Confirm an order
    public function payOrder(Order $order)
    {
        if ($order->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed orders can be pay.',
            ], 400);
        }

        $order->update(['order_status' => 'paid']);

        return response()->json([
            'message' => 'Order confirmed successfully.',
        ]);
    }


    // Confirm an order
    public function refundOrder(Order $order)
    {
        if ($order->status !== 'paid') {
            return response()->json([
                'message' => 'Only paid orders can be refunded.',
            ], 400);
        }

        $order->update(['order_status' => 'refunded']);

        return response()->json([
            'message' => 'Order confirmed successfully.',
        ]);
    }

    // ship an order 

    // Dispatch an order
    public function dispatchOrder(Order $order)
    {
        if ($order->status !== 'paid') {
            return response()->json([
                'message' => 'Only paid orders can be dispatched.',
            ], 400);
        }

        $order->update(['order_status' => 'shipped']);

        return response()->json([
            'message' => 'Order dispatched successfully.',
        ]);
    }

    // Generate and download airway bill

    public function downloadAirwayBill($id)
    {
        // if ($order->status !== 'shipped') {
        //     return response()->json([
        //         'message' => 'Airway bill can only be generated for shipped orders.',
        //     ], 400);
        // }
        $order = Order::find($id);
        // Example order data (replace with actual $order object values)
        $orderData = [
            'warehouse_number' => $order->warehouse_number,
            'total_price' => $order->total_price,
            'order_id' => $order->id,
        ];

        // Generate PDF content using a Blade view
        $pdf = PDF::loadView('pdf.airway_bill', $orderData);

        // Define the PDF file name
        $fileName = "Order_{$order->id}_Airway_Bill.pdf";

        // Return the PDF as a download
        // return $pdf->download($fileName);

        $orderData = [
            'warehouse_number' => $order->warehouse_number,
            'total_price' => $order->total_price,
            'order_id' => $order->id,
        ];

        $pdf = Pdf::loadView('pdf.airway_bill', $orderData);

        return $pdf->download("Order_{$order->id}_Airway_Bill.pdf");
    }

    public function airwayBillText(Order $order)
    {
        // if ($order->status !== 'shipped') {
        //     return response()->json([
        //         'message' => 'Airway bill can only be generated for shipped orders.',
        //     ], 400);
        // }


        $airwayBillContent = "Airway Bill for Order #{$order->warehouse_number}\nTotal Price: {$order->total_price}";

        // Define the file path relative to the storage/app directory
        $filePath = "airway_bills/order_{$order->warehouse_number}.txt";

        // Check if the file exists
        if (!Storage::disk('local')->exists($filePath)) {
            Log::info('File does not exist. Attempting to create.');

            // Ensure the directory exists
            if (!Storage::disk('local')->exists('airway_bills')) {
                Log::info('Directory does not exist. Attempting to create.');
                Storage::disk('local')->makeDirectory('airway_bills');
            }

            // Create the file
            if (!Storage::disk('local')->put($filePath, $airwayBillContent)) {
                Log::error("Failed to create the file at path: {$filePath}");
                return response()->json(['error' => 'Failed to create the file.'], 500);
            }
            Log::info('File successfully created.');
        }

        // Generate the absolute path to the file
        $absoluteFilePath = storage_path("app/{$filePath}");
        Log::info("Absolute file path: {$absoluteFilePath}");

        // Ensure the file exists before attempting to download
        if (!file_exists($absoluteFilePath)) {
            Log::error('File creation failed even though put() was called.');
            return response()->json(['error' => 'Failed to create the file.'], 500);
        }

        // Download the file
        Log::info('File exists. Preparing to download.');
        return response()->download($absoluteFilePath, "Order_{$order->id}_Airway_Bill.txt");
    }

    // ship an order
    public function deliverOrder(Order $order)
    {
        if ($order->status !== 'shipped') {
            return response()->json([
                'message' => 'Only shipped orders can be deliver.',
            ], 400);
        }

        $order->update(['order_status' => 'delivered']);

        return response()->json([
            'message' => 'Order deivered successfully.',
        ]);
    }
}
