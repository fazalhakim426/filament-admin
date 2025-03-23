<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItem extends Model
{

    //timestamp false
    public $timestamps  = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'supplier_user_id',
        'product_variant_id',
        'quantity',
        'price',
        'discount',
        'total'
    ];
    protected static function boot()
    {
        parent::boot();

        OrderItem::created(function ($item) {
            $order = $item->order;
            $order->update([
                'items_cost' => $order->items()->sum(DB::raw('price * quantity')),
            ]); 
            $order->fresh();
            if ($order->need_to_pay == 0) {
                $order->update([
                    'payment_status' => 'pending',
                ]);
            } else {
                $order->update([
                    'payment_status' => 'pending',
                ]);
            }

            InventoryMovement::create([
                'supplier_user_id' => $item->supplier_user_id,
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'type' => 'deduction',
                'quantity' => $item->quantity,
                'unit_price' => $item->price,
                'total_price' => $item->price * $item->quantity
            ]);
        });
        OrderItem::updated(function ($item) {
            $order = $item->order;
            if ($item->quantity == 0) {
                $item->delete();
            }
            $order->update([
                'items_cost' => $order->items()->sum(DB::raw('price * quantity')),
            ]);
            $order->fresh();
            if ($order->need_to_pay == 0) {
                $order->update([
                    'payment_status' => 'pending',
                ]);
            } else {
                $order->update([
                    'payment_status' => 'pending',
                ]);
            }
            $item->inventoryMovement->update(['quantity' => $item->quantity]);
        });
    }


    function order()
    {
        return $this->belongsTo(Order::class);
    }
    function product()
    {
        return $this->belongsTo(Product::class);
    }
    function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    function supplierUser()
    {
        return $this->belongsTo(User::class, 'supplier_user_id');
    }
    function inventoryMovement()
    {
        return $this->hasOne(InventoryMovement::class);
    }
}
