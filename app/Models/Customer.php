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
        return $this->belongsToMany(User::class, 'orders', 'customer_user_id', 'supplier_user_id');
    }
}
