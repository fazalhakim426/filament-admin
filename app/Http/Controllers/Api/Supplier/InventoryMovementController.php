<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVariantResource;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Trait\CustomRespone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryMovementController extends Controller
{
    use CustomRespone;

    function getInventoryMovement($id)
    {
        // $inventoryMovements = $product->inventoryMovements;
        $productVariant = ProductVariant::find($id);
        return $this->json(200, true, $productVariant->sku . ' Inventory Movements', [
            'product_variant' => new ProductVariantResource($productVariant),
            'inventory_movements' => InventoryMovementResource::collection($productVariant->inventoryMovements),
        ]);
    }
    function index()
    {
        return $this->json(200, true, 'Inventory list', InventoryMovementResource::collection(InventoryMovement::where('supplier_user_id', Auth::id())->with('product.productVariants.variantOptions')->get()));
    }
    function show($id)
    {
        $inventoryMovement = InventoryMovement::where('id', $id)->where('supplier_user_id', Auth::id())->first();
        return $this->json(200, true, 'Inventory details', new InventoryMovementResource($inventoryMovement));
    }
    function store(InventoryMovementRequest $request)
    {
        $productVariant = ProductVariant::find($request->product_variant_id);

        $validated = $request->validated() + [
            'supplier_user_id' =>  Auth::id(),
            'product_id' => $productVariant->product_id,
        ];

        $inventoryMovement = InventoryMovement::create($validated);
        return $this->json(
            200,
            true,
            new InventoryMovementResource($inventoryMovement->load('product')),
        );
    }
    function destroy(InventoryMovement $inventoryMovement)
    {
        $inventoryMovement->delete();
        return $this->json(200, true, 'Inventory Movement deleted successfully.');
    }
}
