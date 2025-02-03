<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        'status',
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
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->warehouse_number = self::generateWarehouseNumber();
            $order->total_price =  $order->calculateTotalPrice();
        });
        static::updated(function ($order) {
            $order->total_price =  $order->calculateTotalPrice();
        });
    }
    function getTotalPriceAttribute() {
        return $this->products()->sum(DB::raw('price * quantity'));
    }

    private static function generateWarehouseNumber()
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
    public function calculateTotalPrice()
    {
        return $this->items()->sum(DB::raw('price * quantity'));
    }

   
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class,'order_id');
    }

    public function getPaidAttribute()
    {
        return $this->deposits()
            ->where('transaction_type', 'debit')
            ->sum('amount')-$this->deposits()
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
}
