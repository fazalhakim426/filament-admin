<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $fillable = [
        'warehouse_number',
        'customer_user_id',
        'recipient_id',
        'sender_id',
        'total_price',
        'items_cost',
        'shipping_cost',
        'order_status',
        'created_at',
        'updated_at',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')->withPivot(['id', 'supplier_user_id', 'quantity', 'price']);
    }

    function user()
    {
        return $this->belongsTo(User::class);
    }
    function supplierUser()
    {
        return $this->belongsTo(User::class, 'supplier_user_id');
    }
    function customerUser()
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }
    function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
    function trackings()
    {
        return $this->hasMany(OrderTracking::class, 'order_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::updating(function ($order) {});
    }
    public function deductInventory()
    {
        foreach ($this->items as $item) {
            $product = $item->product;

            if ($product->stock_quantity < $item->quantity) {
                throw new \Exception("Not enough stock for product ID: {$product->id}");
            }

            $product->decrement('stock_quantity', $item->quantity);

            InventoryMovement::create([
                'supplier_user_id' => $product->supplier_user_id,
                'product_id' => $product->id,
                'order_item_id' => $item->id,
                'type' => 'deduction',
                'quantity' => $item->quantity,
                'unit_price' => $item->price,
                'total_price' => $item->quantity * $item->price,
                'description' => "Deducted for order #{$this->warehouse_number}",
            ]);
        }
    }

    public function addInventory()
    {
        foreach ($this->items as $item) {
            $product = $item->product;

            $product->increment('stock_quantity', $item->quantity);

            InventoryMovement::create([
                'supplier_user_id' => $product->supplier_user_id,
                'product_id' => $product->id,
                'order_item_id' => $item->id,
                'type' => 'addition',
                'quantity' => $item->quantity,
                'unit_price' => $item->price,
                'total_price' => $item->quantity * $item->price,
                'description' => "Restocked for order #{$this->warehouse_number} - status: {$this->order_status}",
            ]);
        }
    }



    function generateWarehouseNumber()
    {
        // Generate a 13-digit number using a combination of timestamp and random digits
        $timestamp = now()->format('md'); // 8 digits: Current date (YYYYMMDD)
        $randomNumber = str_pad(mt_rand(0, 999999), 5, '0', STR_PAD_LEFT); // 5 digits: Random padded to ensure length

        return "WH{$timestamp}{$randomNumber}PK";
    }
    function sender()
    {
        return $this->belongsTo(Address::class, 'sender_id');
    }
    function recipient()
    {
        return $this->belongsTo(Address::class, 'recipient_id');
    }


    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'order_id');
    }

    public function getPaidAttribute()
    {
        return $this->deposits()
            ->where('transaction_type', 'debit')
            ->sum('amount') - $this->deposits()
            ->where('transaction_type', 'credit')
            ->sum('amount');
    }

    public function getRefundedAttribute()
    {
        return $this->deposits()
            ->where('transaction_type', 'credit')
            ->sum('amount');
    }

    public function getNeedToPayAttribute()
    {
        return $this->total_price  - $this->paid;
    }
    public function getStatusAttribute()
    {
        if ($this->payment_status === 'refunded' || $this->order_status === 'canceled') {
            return 'canceled';
        }

        if ($this->payment_status === 'unpaid') {
            return 'pending';
        }

        if ($this->order_status === 'delivered' && $this->payment_status === 'paid') {
            return 'completed';
        }

        return $this->order_status;
    }
    function reCalculate()
    {
        $this->items_cost = $this->items()->sum(DB::raw('price * quantity'));
        $this->total_price = $this->shipping_cost + $this->items_cost;
        $this->save();
    }
}
