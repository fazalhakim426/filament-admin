<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
        return OrderResource::collection($user->orderAsCustomer->load('items.product'));
    }
    function show(Order $order)
    {
        return new OrderResource($order->load('items.product', 'customerUser', 'deposits'));
    }
    public function store(Request $request)
    {
        $request->validate([
            // 'total_price' => 'required|numeric', 
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // 'items.*.price' => 'required|numeric',
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

            $order = Order::create([
                'warehouse_number' => $request->warehouse_number,
                'total_price' => '-1',
                'customer_user_id' => auth()->id(),
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'order_status' => 'new',
                'payment_status' => 'unpaid'
            ]);
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    return $this->rollbackWithResponse('Product out of stock', 422);
                }
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->unit_selling_price,
                    'supplier_user_id' => $product->supplier_user_id,
                    'order_status' => 'pending'
                ]);
            }
            DB::commit();
            $order->refresh();
            return $this->json(200, true, 'Order created successfully', new OrderResource($order->load(['items', 'sender', 'recipient'])));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->json(500, false, 'Failed to create order:' . $e->getMessage(), null,);
        }
    }
    public function updateProductQantity(Request $request, Order $order)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->items as $itemRequest) { 
                $orderItem = OrderItem::where('product_id', $itemRequest['product_id'])->where('order_id', $order->id)->first();

                if ($orderItem) {
                    $product = $orderItem->product;
                    if ($itemRequest['quantity'] < 0 && ($itemRequest['quantity'] * (-1)) > $orderItem->quantity) {
                        return $this->rollbackWithResponse('quantity exceed. Not enough items found', 422);
                    }
                    if ($product->stock_quantity < $itemRequest['quantity']) {
                        return $this->rollbackWithResponse('Product out of stock', 422);
                    }
                    $orderItem->update([
                        'quantity' =>$itemRequest['quantity']
                    ]);
                } else {
                    $product = Product::find($itemRequest['product_id']);
                    if (!$product) {
                        return $this->rollbackWithResponse('Product not found', 404);
                    }

                    if ($product->stock_quantity < $itemRequest['quantity']) {
                        return $this->rollbackWithResponse('Product out of stock.', 422);
                    }
                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $itemRequest['quantity'],
                        'price' => $product->unit_selling_price,
                        'supplier_user_id' => $product->supplier_user_id,
                        'order_status' => 'pending',
                    ]);
                }
            }

            DB::commit();
            $order->refresh();
            return $this->json(200, true, 'Order updated successfully', new OrderResource($order->load(['items', 'sender', 'recipient'])));
        } catch (\Exception $e) {
            return $this->rollbackWithResponse('Failed: ' . $e->getMessage(), 500);
        }
    }
    function removeItems(OrderItem $orderItem)
    {
        dd($orderItem);
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
