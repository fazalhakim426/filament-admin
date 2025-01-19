<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    function user() {
        return $this->belongsTo(User::class);
    }
     
    function supplier() {
        return $this->belongsTo(User::class, 'supplier_user_id');
    }
    function reseller() {
        return $this->belongsTo(User::class, 'reseller_user_id');
    }
    function orderItem() {
        return $this->belongsTo(OrderItem::class,'order_item_id');
    }
}
