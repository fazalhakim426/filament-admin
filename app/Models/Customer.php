<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    function orders() {
        return $this->hasMany(Order::class);
    }
    function suppliers() {
        return $this->belongsToMany(Supplier::class, 'orders', 'customer_id', 'supplier_id');
    }
}
