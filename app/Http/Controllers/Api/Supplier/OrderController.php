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
    public function streamAirwayBill(Order $order)
    {
        $pdf =  Pdf::loadView('pdf.airway_bill', $this->airwaybillData($order));
        return $pdf->stream("Order_{$order->id}_Airway_Bill.pdf");
    }
    public function downloadAirwayBill(Order $order)
    {
        $pdf =  Pdf::loadView('pdf.airway_bill', $this->airwaybillData($order));
        return $pdf->download("Order_{$order->id}_Airway_Bill.pdf");
    }
    public function airwaybillData(Order $order)
    {
        $barcodeUrl = 'https://barcode.tec-it.com/barcode.ashx?data=20044512489503&code=Code128&translate-esc=true';
        $barcodeContent = file_get_contents($barcodeUrl);
        // Convert it to base64
        $barcodeBase64 = 'data:image/png;base64,' . base64_encode($barcodeContent);

        $postExBarcodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=sample';
        $postExContent = file_get_contents($postExBarcodeUrl);
        $postBarcodeBase64 = 'data:image/png;base64,' . base64_encode($postExContent);
        $recipient = $order->recipient;
        $sender = $order->sender; 

        $orderItems=[];
        foreach($order->items as $item){
            $orderItems[]=[
                'product_name' => $item->productVariant->product->name,
                'sku' => $item->productVariant->sku,
                'variant_name' => $item->productVariant->variantOptions->map(function ($option) {
                    return $option->name;
                })->implode(', '),
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        }
        
        $order->items->map(function ($item) {
            return [
                'product_name' => $item->productVariant->product->name,
                'variant_name' => $item->productVariant->variantOptions->map(function ($option) {
                    return $option->name;
                })->implode(', '),
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        });
        return [
            'barcode' => $barcodeBase64,
            'post_ex_barcode' => $postBarcodeBase64,
            'warehouse_number' => $order->warehouse_number,
            'total_price' => $order->total_price,
            'shipping_cost' => $order->shipping_cost,
            'items_cost' => $order->items_cost,
            'order_id' => $order->id,
            'total_price' => $order->total_price, 
            'destination' => [
                'name' => $recipient->name,
                'address' => $recipient->address,
                'phone' => $recipient->phone,
                'zip' => $recipient->zip,
                'street' => $recipient->street,
                'country' => $recipient->country->name,
                'state' => $recipient->state->name,
                'city' => $recipient->city->name,
            ],
            'sender' => [
                'name' => $sender->name,
                'street' => $recipient->street,
                'address' => $sender->address,
                'phone' => $recipient->phone,
                'zip' => $sender->zip,
                'country' => $sender->country->name,
                'state' => $sender->state->name,
                'city' => $sender->city->name,
            ],
            'items_count' => $order->items->count(),
            'items' => $orderItems,
        ];
    }
}
