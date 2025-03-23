<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Trait\CustomRespone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\json;

class PlaceOrderController extends Controller
{
    use CustomRespone;
    function index()
    {
        $user = Auth::user();
        return OrderResource::collection($user->orderAsCustomer->load([
            'items.productVariant.product',
            'items.productVariant.variantOptions',
            'sender',
            'recipient',
            'deposits'
        ]));
    }
    function show(Order $order)
    {
        return new OrderResource($order->load([
            'items.productVariant.product',
            'items.productVariant.variantOptions',
            'sender',
            'recipient',
            'deposits'
        ]));
    }
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'items' => 'required|array',
    //         'items.*.product_variant_id' => 'required|exists:product_variants,id',
    //         'items.*.quantity' => 'required|integer|min:1',
    //         'sender_id' => 'nullable|exists:addresses,id',
    //         'recipient_id' => 'nullable|exists:addresses,id',
    //         'sender' => 'nullable|array',
    //         'recipient' => 'nullable|array',
    //     ], [], [
    //         'sender_id' => 'sender address ID',
    //         'recipient_id' => 'recipient address ID',
    //         'sender' => 'sender address',
    //         'recipient' => 'recipient address',
    //     ]);

    //     if (!$request->filled('sender_id') && !$request->filled('sender')) {
    //         throw ValidationException::withMessages([
    //             'sender_id' => ['Either sender_id or sender address details must be provided.'],
    //         ]);
    //     }

    //     if (!$request->filled('recipient_id') && !$request->filled('recipient')) {
    //         throw ValidationException::withMessages([
    //             'recipient_id' => ['Either recipient_id or recipient address details must be provided.'],
    //         ]);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $senderId = $request->sender_id;
    //         $recipientId = $request->recipient_id;

    //         if (!$senderId && $request->filled('sender')) {
    //             $sender = Address::create(array_merge($request->sender, ['user_id' => auth()->id()]));
    //             $senderId = $sender->id;
    //         }

    //         if (!$recipientId && $request->filled('recipient')){
    //             $recipient = Address::create(array_merge($request->recipient, ['user_id' => auth()->id()]));
    //             $recipientId = $recipient->id;
    //         }

    //         $order = Order::create([
    //             'warehouse_number' => $request->warehouse_number,
    //             'total_price' => '-1',
    //             'items_cost' => '-1',
    //             'shipping_cost' => '-1',
    //             'customer_user_id' => auth()->id(),
    //             'sender_id' => $senderId,
    //             'recipient_id' => $recipientId,
    //             'order_status' => 'new',
    //             'payment_status' => 'unpaid'
    //         ]);

    //         foreach ($request->items as $item) {
    //             $variant = ProductVariant::find($item['product_variant_id']);
    //             // if ($variant->stock_quantity < $item['quantity']) {
    //             //     return $this->rollbackWithResponse('Product variant out of stock', 422);
    //             // }

    //             $order->items()->create([
    //                 'product_variant_id' => $variant->id,
    //                 'product_id' => $variant->product_id,
    //                 'quantity' => $item['quantity'],
    //                 'price' => $variant->unit_selling_price,
    //                 'supplier_user_id' => $variant->product->supplier_user_id,
    //                 'order_status' => 'pending'
    //             ]);
    //         }

    //         DB::commit();
    //         $order->refresh();

    //         return $this->json(200, true, 'Order created successfully', new OrderResource($order->load(['items.productVariant.product','items.productVariant.variantOptions', 'sender', 'recipient','deposits'])));
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->json(500, false, 'Failed to create order: ' . $e->getMessage(), null);
    //     }
    // }
    public function store(Request $request)
    { 

        $request->validate([
            'items' => 'required|array',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'sender_id' => 'nullable|exists:addresses,id',
            'recipient_id' => 'nullable|exists:addresses,id',
            'sender' => 'nullable|array',
            'recipient' => 'nullable|array',
        ], [], [
            'sender_id' => 'sender address ID',
            'recipient_id' => 'recipient address ID',
            'sender' => 'sender address',
            'recipient' => 'recipient address',
        ]);
        if (!$request->filled('sender_id') && !$request->filled('sender')) {
            throw ValidationException::withMessages([
                'sender_id' => ['Either sender_id or sender address details must be provided.'],
            ]);
        }

        if (!$request->filled('recipient_id') && !$request->filled('recipient')) {
            throw ValidationException::withMessages([
                'recipient_id' => ['Either recipient_id or recipient address details must be provided.'],
            ]);
        }

        DB::beginTransaction();
        try {
            $senderId = $request->sender_id;
            $recipientId = $request->recipient_id;

            if (!$senderId && $request->filled('sender')) {
                $sender = Address::create(array_merge($request->sender, ['user_id' => auth()->id()]));
                $senderId = $sender->id;
            }

            if (!$recipientId && $request->filled('recipient')) {
                $recipient = Address::create(array_merge($request->recipient, ['user_id' => auth()->id()]));
                $recipientId = $recipient->id;
            }

            $itemsGroupedBySupplier = collect($request->items)->groupBy(function ($item) {
                return ProductVariant::find($item['product_variant_id'])->product->supplier_user_id;
            });

            $orders = [];
            foreach ($itemsGroupedBySupplier as $supplierId => $items) {
                $order = Order::create([
                    'warehouse_number' => $request->warehouse_number, 
                    'customer_user_id' => auth()->id(),
                    'supplier_user_id' => $supplierId,
                    'sender_id' => $senderId,
                    'shipping_cost' => '10',
                    'recipient_id' => $recipientId,
                    'order_status' => 'new',
                    'payment_status' => 'unpaid'
                ]);

                foreach ($items as $item) {
                    $variant = ProductVariant::find($item['product_variant_id']);
                    $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'quantity' => $item['quantity'],
                        'price' => $variant->unit_selling_price,
                        'supplier_user_id' => $variant->product->supplier_user_id,
                        'order_status' => 'pending'
                    ]);
                }
                $order->refresh();

                $orders[] = $order;
            }

            DB::commit();
            return $this->json(200, true, 'Orders created successfully', OrderResource::collection($orders));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->json(500, false, 'Failed to create orders: ' . $e->getMessage(), null);
        }
    }

    public function updateProductQuantity(Request $request, Order $order)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->items as $itemRequest) {
                $orderItem = OrderItem::where('product_variant_id', $itemRequest['product_variant_id'])
                    ->where('order_id', $order->id)
                    ->first();

                if ($orderItem) {
                    $variant = $orderItem->productVariant;

                    // Check if the quantity is valid
                    if ($itemRequest['quantity'] < 0 && abs($itemRequest['quantity']) > $orderItem->quantity) {
                        return $this->rollbackWithResponse('Quantity exceed. Not enough items found', 422);
                    }

                    if ($variant->stock_quantity < $itemRequest['quantity']) {
                        return $this->rollbackWithResponse('Product variant out of stock', 422);
                    }

                    $orderItem->update([
                        'quantity' => $itemRequest['quantity']
                    ]);
                } else {
                    $variant = ProductVariant::find($itemRequest['product_variant_id']);
                    if (!$variant) {
                        return $this->rollbackWithResponse('Product variant not found', 404);
                    }

                    if ($variant->stock_quantity < $itemRequest['quantity']) {
                        return $this->rollbackWithResponse('Product variant out of stock.', 422);
                    }

                    $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $itemRequest['quantity'],
                        'price' => $variant->unit_selling_price,
                        'supplier_user_id' => $variant->product->supplier_user_id, // Get supplier from the main product
                        'order_status' => 'pending',
                    ]);
                }
            }

            DB::commit();
            $order->refresh();
            return $this->json(200, true, 'Order updated successfully', new OrderResource($order->load(['items.productVariant.product', 'items.productVariant.variantOptions', 'sender', 'recipient', 'deposits'])));
        } catch (\Exception $e) {
            return $this->rollbackWithResponse('Failed: ' . $e->getMessage(), 500);
        }
    }

    function removeItems(OrderItem $orderItem)
    {
        $orderItem->delete();
        return $this->json(200, true, 'Order item removed successfully');
        try {
        } catch (\Exception $e) {
            return $this->rollbackWithResponse('Failed : ' . $e->getMessage(), 500);
        }
    }
    function rollbackWithResponse($message, $status)
    {
        DB::rollBack();
        return $this->json($status, false, $message);
    }
}
