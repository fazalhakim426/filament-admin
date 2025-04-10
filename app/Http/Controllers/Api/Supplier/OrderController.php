<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller; 
use App\Http\Resources\OrderResource; 
use App\Models\Order;
use App\Models\OrderItem;
use App\Trait\CustomRespone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
class OrderController extends Controller
{
    use CustomRespone;

    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('supplier_user_id', $user->id)->with([
            'items.productVariant.product',
            'items.productVariant.variantOptions',
            'sender',
            'recipient',
            'deposits',
            'trackings'
        ])
        ->paginate(request('per_page', 15));

        return $this->json(200, true, 'Order retrieved successfully.', [
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
            'links' => [
                'first' => $orders->url(1),
                'last' => $orders->url($orders->lastPage()),
                'prev' => $orders->previousPageUrl(),
                'next' => $orders->nextPageUrl(),
            ]
        ]); 
    }

    // Show a specific order
    public function show(Order $order)
    {
        $order->load('orderItems.product');

        return $this->json(200, true, 'Order details retrieved successfully.', new OrderResource($order->load(
            [
                'items.productVariant.product',
                'items.productVariant.variantOptions',
                'sender',
                'recipient',
                'deposits'
            ]
        )));
    }

    // Confirm an order
    // public function confirmOrder(Order $order)
    // {
    //     if ($order->status !== 'pending') {
    //         return $this->json(400,false, 'Only pending orders can be confirmed.');
    //     }

    //     $order->update(['order_status' => 'confirmed']);

    //     return $this->json(200,true,'Order confirmed successfully.');
    // }

    // Confirm an order
    public function confirmOrderItem($id)
    {
        $item = OrderItem::find($id);
        if ($item->status !== 'pending') {
            return $this->json(400, false, 'Only pending orders can be confirmed.');
        }
        $item->update(['order_status' => 'confirmed']);

        return $this->json(200, true, 'Order item confirmed successfully.');
    }


    // Confirm an order
    public function rejectOrderItem($id)
    {
        $item = OrderItem::find($id);
        if ($item->status !== 'pending') {
            return $this->json(400, false, 'Only pending orders can be rejected.');
        }
        $item->update(['order_status' => 'canceled']);

        return $this->json(200, true, 'Order item canceled successfully.');
    }

    // Confirm an order
    public function payOrder(Order $order)
    {
        if ($order->status !== 'confirmed') {
            return $this->json(400, false, 'Only confirmed orders can be pay.');
        }

        $order->update(['order_status' => 'paid']);

        return $this->json(200, true, 'Order confirmed successfully.');
    }


    // Confirm an order
    public function refundOrder(Order $order)
    {
        if ($order->status !== 'paid') {
            return $this->json(400, false, 'Only paid orders can be refunded.');
        }

        $order->update(['order_status' => 'refunded']);

        return $this->json(200, true, 'Order confirmed successfully.');
    }

    // ship an order 

    // Dispatch an order
    public function dispatchOrder(Order $order)
    {
        if ($order->status !== 'paid') {
            return $this->json(400, false, 'Only paid orders can be dispatched.');
        }

        $order->update(['order_status' => 'shipped']);

        return $this->json(200, true, 'Order dispatched successfully.');
    }

    // Generate and download airway bill

    public function downloadAirwayBill(Order $order)
    {
        // if ($order->status !== 'shipped') {
        //     return $this->json([
        //         'message' => 'Airway bill can only be generated for shipped orders.',
        //     ], 400);
        // } 
        // Example order data (replace with actual $order object values)
        $orderData = [
            'warehouse_number' => $order->warehouse_number,
            'total_price' => $order->total_price,
            'shipping_cost' => $order->shipping_cost,
            'items_cost' => $order->items_cost,
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
        //     return $this->json([
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
                return $this->json(500, false, 'Failed to create the file.');
            }
            Log::info('File successfully created.');
        }

        // Generate the absolute path to the file
        $absoluteFilePath = storage_path("app/{$filePath}");
        Log::info("Absolute file path: {$absoluteFilePath}");

        // Ensure the file exists before attempting to download
        if (!file_exists($absoluteFilePath)) {
            Log::error('File creation failed even though put() was called.');
            return $this->json(500, false, 'Failed to create the file.');
        }

        // Download the file
        Log::info('File exists. Preparing to download.');
        return $this->download($absoluteFilePath, "Order_{$order->id}_Airway_Bill.txt");
    }

    // ship an order
    public function deliverOrder(Order $order)
    {
        if ($order->status !== 'shipped') {
            return $this->json(400, true, 'Only shipped orders can be deliver.');
        }

        $order->update(['order_status' => 'delivered']);

        return $this->json(200, true, 'Order deivered successfully.');
    }
}
