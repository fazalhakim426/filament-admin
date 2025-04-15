<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

    use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{

    use HasFactory;
    protected $fillable = ['referrel_reward_amount', 'referrel_reward_percentage', 'supplier_user_id', 'category_id', 'name', 'description', 'selling_price', 'price', 'stock_quantity', 'sku', 'is_active'];


    function supplierUser()
    {
        return $this->belongsTo(User::class, 'supplier_user_id');
    }
    function category()
    {
        return $this->belongsTo(Category::class);
    }

    function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    function order()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')->withPivot('amount');
    }

    function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted()
    {
        static::deleted(function ($product) {
            $product->images()->each(function ($image) {
                \Storage::disk('public')->delete($image->url); // Delete the image file
                $image->delete(); // Delete the image record
            });
        });
    }
    // public function images(): Attribute
    // {
    //     return Attribute::set(fn($value) => collect($value)->map(fn($file) => ['url' => $file]));
    // }
 
    function reviews() {
        return $this->hasMany(Review::class);
    }
    function inventoryMovements() {
        return $this->hasMany(InventoryMovement::class);
    }
    function productVariants() {
        return $this->hasMany(ProductVariant::class);
    }
    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }
}
