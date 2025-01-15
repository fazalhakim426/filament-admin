<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDetail extends Model
{
    use HasFactory;
    function products()
    {
        return $this->hasMany(Product::class);
    }
    function orders()
    {
        return $this->hasMany(Order::class);
    }
    //cutomer through order 
    function customers()
    {
        return $this->belongsToMany(Customer::class, 'orders', 'supplier_id', 'customer_id');
    }
    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
