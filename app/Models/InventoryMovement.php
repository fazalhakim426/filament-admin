<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'stock_quantity', 'product_id', 'type', 'stock_quantity', 'unit_cost_price'];
    function product()
    {
        return $this->belongsTo(Product::class);
    } 
    function supplierUser()
    {
        return $this->belongsTo(User::class,'supplier_user_id');
    }
    protected static function booted()
    {
        static::creating(function ($inventoryMovement) {
            $product = $inventoryMovement->product;
            if ($product) {
                //log
                if ($inventoryMovement->type == 'addition') {
                    $product->update(['stock_quantity' => ($product->stock_quantity + $inventoryMovement->quantity)]);
                } elseif ($inventoryMovement->type == 'deduction') {

                    $product->update(['stock_quantity' => ($product->stock_quantity - $inventoryMovement->quantity)]);
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
                        throw new \Exception('Stock already sold.');
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
