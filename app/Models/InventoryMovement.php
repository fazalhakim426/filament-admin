<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_user_id',
        'product_id',
        'order_item_id',
        'type',
        'quantity',
        'unit_price',
        'total_price',
        'description',
    ];
    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = $value;
        $this->calculateTotalPrice();
    }

    public function setUnitPriceAttribute($value)
    {
        $this->attributes['unit_price'] = $value;
        $this->calculateTotalPrice();
    }

    protected function calculateTotalPrice()
    {
        $quantity = $this->attributes['quantity'] ?? 0;
        $unitPrice = $this->attributes['unit_price'] ?? 0;

        $this->attributes['total_price'] = $quantity * $unitPrice;
    }
    function product()
    {
        return $this->belongsTo(Product::class);
    }
    function orderItem(){
        return $this->belongsTo(OrderItem::class);
    }
    function supplierUser()
    {
        return $this->belongsTo(User::class, 'supplier_user_id');
    }
    protected static function booted()
    {
        static::creating(function ($inventoryMovement) {
                    $product = $inventoryMovement->product;
                    if ($product){
                        if ($inventoryMovement->type == 'addition'){
                            $product->update([
                                'stock_quantity' => ($product->stock_quantity + $inventoryMovement->quantity)
                            ]);
                        } elseif($inventoryMovement->type == 'deduction') {
                            if($product->stock_quantity < $inventoryMovement->quantity) {
                                Log::error('Stock Deduction Failed - Not Enough Stock', [
                                    'stock_quantity' => $product->stock_quantity,
                                    'requested_quantity' => $inventoryMovement->quantity,
                                ]);
                                throw new \Exception('Not enough stock.');
                            }else {
                                $product->update([
                                    'stock_quantity' => ($product->stock_quantity - $inventoryMovement->quantity)
                                ]); 
                            }
                        }
                    } else {
                        throw new \Exception('Product not found for Inventory Movement.');
                    }
         });

        static::deleting(function ($inventoryMovement) {
            $product = $inventoryMovement->product;
            if ($product) {

                if ($inventoryMovement->type == 'addition') {
                    if ($product->stock_quantity < $inventoryMovement->quantity) {
                        throw new \Exception('Not enough stock.');
                    }
                    $product->update(['stock_quantity' => ($product->stock_quantity - $inventoryMovement->quantity)]);
                } elseif ($inventoryMovement->type == 'deduction') {
                    $product->update(['stock_quantity' => ($product->stock_quantity + $inventoryMovement->quantity)]);
                }
            } else {
                throw new \Exception('Product not found for Inventory Movement.');
            }
        });

        static::updating(function ($inventoryMovement) {
            $product = $inventoryMovement->product;
            if ($product) {
                // Revert the previous stock adjustment
                $previousStockQuantity = $inventoryMovement->getOriginal('quantity');
                $previousType = $inventoryMovement->getOriginal('type');

                if ($previousType == 'addition') {
                    $product->update(['stock_quantity' => $product->stock_quantity - $previousStockQuantity]);
                } elseif ($previousType == 'deduction') {
                    $product->update(['stock_quantity' => $product->stock_quantity + $previousStockQuantity]);
                }
                // Apply the new stock adjustment
                if ($inventoryMovement->type == 'addition') {
                    $product->update(['stock_quantity' => $product->stock_quantity + $inventoryMovement->quantity]);
                } elseif ($inventoryMovement->type == 'deduction') {
                    $product->update(['stock_quantity' => $product->stock_quantity - $inventoryMovement->quantity]);
                }
            } else {
                throw new \Exception('Product not found for Inventory Movement.');
            }
        });
    }
}
