<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->warehouse_number = $order->generateWarehouseNumber();
    }

    public function created(Order $order): void
    {
        OrderTracking::create([
            'order_id' => $order->id,
            'status' => $order->order_status,
            'note' => 'Order status updated to ' . $order->order_status,
        ]);
        $order->updateQuietly([
            'total_price' => ($order->items_cost + $order->shipping_cost + $order->items_commission) - $order->items_discount
        ]);
        if ($order->isDirty('order_status')) {
            $originalStatus = $order->getOriginal('order_status');
            $newStatus = $order->order_status;

            Log::info("Order status changed from $originalStatus to $newStatus");

            if ($newStatus === 'confirmed' && $originalStatus !== 'confirmed') {
                $order->deductInventory();
            }
            if (in_array($newStatus, ['canceled', 'refunded']) && !in_array($originalStatus, ['canceled', 'refunded'])) {
                $order->addInventory();
            }
        }

        if ($order->isDirty('items_cost')) {
            $order->updateQuietly([
                'items_cost' => $order->items()->sum(DB::raw('price * quantity')),
            ]);
        }
        if ($order->isDirty('items_commission')) {
            $order->updateQuietly([
                'items_commission' => $order->items()->sum(DB::raw('commission * quantity')),
            ]);
        }
        if ($order->isDirty('items_discount')) {
            $order->updateQuietly([
                'items_discount' => $order->items()->sum(DB::raw('discount * quantity')),
            ]);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updating(Order $order)
    {
        // Check if the order status is changed
        if ($order->isDirty('order_status')) {
            OrderTracking::create([
                'order_id' => $order->id,
                'status' => $order->order_status,
                'note' => 'Order status updated to ' . $order->order_status,
            ]);
        }
        if ($order->isDirty('shipping_cost') || $order->isDirty('items_cost') || $order->isDirty('items_commission') || $order->isDirty('items_discount')) {
            $order->updateQuietly(['total_price' => ($order->shipping_cost + $order->items_cost  + $order->items_commission) - $order->items_discount]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
