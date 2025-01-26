<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryMovementController extends Controller
{

    function store(InventoryMovementRequest $request)
    {
        $validated = $request->validated() + [
            'supplier_user_id' =>  Auth::id(),
        ];

        $inventoryMovement = InventoryMovement::create($validated);
        return response()->json(
            new InventoryMovementResource($inventoryMovement->load('product')),
        );
    }
    function index()
    {
        return response()->json(
            InventoryMovementResource::collection(InventoryMovement::where('supplier_user_id', Auth::id())->with('product')->get())
        );
    }
    function show($id)
    {
        $inventoryMovement = InventoryMovement::where('id', $id)->where('supplier_user_id', Auth::id())->first();
        return response()->json(
            new InventoryMovementResource($inventoryMovement)
        );
    }
    function destroy(InventoryMovement $inventoryMovement)
    {
        $inventoryMovement->delete();
        return response()->json(
            ['message' => 'Inventory Movement deleted successfully.']
        );
    }
}
