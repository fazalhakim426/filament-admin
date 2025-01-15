<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'price', 'discount', 'total'
    ];
    function order() {
        return $this->belongsTo(Order::class);
    }
    function product() {
        return $this->belongsTo(Product::class);
    }
    function supplierUser() {
        return $this->belongsTo(User::class,'supplier_user_id');
    }
}
