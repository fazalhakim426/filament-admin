<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    
    use HasFactory;
    

    function category() {
        return $this->belongsTo(Category::class);
    }
    function supplier() {
        return $this->belongsTo(Supplier::class);
    }
    function order() {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')->withPivot('amount');
    }

}
