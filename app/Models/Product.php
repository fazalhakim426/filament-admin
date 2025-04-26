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

   

    function reviews()
    {
        return $this->hasMany(Review::class);
    }
    function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
    function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }
    // In your Product model
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
