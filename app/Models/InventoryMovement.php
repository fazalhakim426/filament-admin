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
            $productVariant = $inventoryMovement->productVariant; 
            if ($productVariant) {
                if ($inventoryMovement->type == 'addition') {
                    $productVariant->update([
                        'stock_quantity' => ($productVariant->stock_quantity + $inventoryMovement->quantity)
                    ]);
                } elseif ($inventoryMovement->type == 'deduction') {
                    if ($productVariant->stock_quantity < $inventoryMovement->quantity) {
                        Log::error('Stock Deduction Failed - Not Enough Stock', [
                            'stock_quantity' => $productVariant->stock_quantity,
                            'requested_quantity' => $inventoryMovement->quantity,
                        ]);
                        
                        throw new \Exception('Not enough stock.');
                    } else {
                        $productVariant->update([
                            'stock_quantity' => ($productVariant->stock_quantity - $inventoryMovement->quantity)
                        ]);
                    }
                }
            } else {
                throw new \Exception('Product Variant not found for Inventory Movement.');
            }
        });
    
        static::deleting(function ($inventoryMovement) {
            $productVariant = $inventoryMovement->productVariant;
            if ($productVariant) {
                if ($inventoryMovement->type == 'addition') {
                    if ($productVariant->stock_quantity < $inventoryMovement->quantity) {
                        throw new \Exception('Not enough stock.');
                    }
                    $productVariant->update(['stock_quantity' => ($productVariant->stock_quantity - $inventoryMovement->quantity)]);
                } elseif ($inventoryMovement->type == 'deduction') {
                    $productVariant->update(['stock_quantity' => ($productVariant->stock_quantity + $inventoryMovement->quantity)]);
                }
            } else {
                throw new \Exception('Product Variant not found for Inventory Movement.');
            }
        });
    
        static::updating(function ($inventoryMovement) {
            $productVariant = $inventoryMovement->productVariant;
            if ($productVariant) {
                // Revert the previous stock adjustment
                $previousStockQuantity = $inventoryMovement->getOriginal('quantity');
                $previousType = $inventoryMovement->getOriginal('type');
    
                if ($previousType == 'addition') {
                    $productVariant->update(['stock_quantity' => $productVariant->stock_quantity - $previousStockQuantity]);
                } elseif ($previousType == 'deduction') {
                    $productVariant->update(['stock_quantity' => $productVariant->stock_quantity + $previousStockQuantity]);
                }
                // Apply the new stock adjustment
                if ($inventoryMovement->type == 'addition') {
                    $productVariant->update(['stock_quantity' => $productVariant->stock_quantity + $inventoryMovement->quantity]);
                } elseif ($inventoryMovement->type == 'deduction') {
                    $productVariant->update(['stock_quantity' => $productVariant->stock_quantity - $inventoryMovement->quantity]);
                }
            } else {
                throw new \Exception('Product Variant not found for Inventory Movement.');
            }
        });
    }
    
    
    function productVariant() {
        return $this->belongsTo(ProductVariant::class);
    }
}
