<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'stock_quantity',
        'unit_selling_price',
        'sku',
    ];
    function variantOptions() {
        return $this->hasMany(VariantOption::class);
    }
    function product() {
        return $this->belongsTo(Product::class);
    }
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function inventoryMovements() {
        return $this->hasMany(InventoryMovement::class);
    }
}
