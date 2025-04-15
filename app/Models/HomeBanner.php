<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    
    function product() {
        return $this->belongsTo(Product::class,'display_order');
    }
}
