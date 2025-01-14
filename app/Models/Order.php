<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    //product using pivot order_items
    function products() {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')->withPivot('amount');
    }
}
