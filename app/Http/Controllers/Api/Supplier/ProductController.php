<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    use AuthorizesRequests;
    public function index()
    {
        // Retrieve parameters
        $params = request()->only(['search', 'name', 'sku', 'description', 'stock_quantity', 'unit_selling_price', 'category_id', 'is_active']);
 
        $query = Product::where('supplier_user_id', Auth::id());

        // Apply filters dynamically
        if (!empty($params['search'])) {
            $query->where('name', 'like', "%{$params['search']}%");
        }
        if (!empty($params['search'])) {
            $query->where('name', 'like', "%{$params['search']}%");
        }
        if (!empty($params['description'])) {
            $query->where('description', 'like', "%{$params['description']}%");
        }
        if (!empty($params['unit_selling_price'])) {
            $query->where('unit_selling_price', 'like', "%{$params['unit_selling_price']}%");
        }
        if (!empty($params['referral_reward_percentage'])) {
            $query->where('referral_reward_percentage', 'like', "%{$params['referral_reward_percentage']}%");
        }
        if (!empty($params['stock_quantity'])) {
            $query->where('stock_quantity', $params['stock_quantity']);
        }
        if (!empty($params['sku'])) {
            $query->where('sku', 'like', "%{$params['sku']}%");
        }
        if (!empty($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']=='true'?true:false);
        } 

        return ProductResource::collection($query->get());
    }

    // Store a newly created product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'referral_reward_amount' => 'nullable|numeric|min:0',
            'referral_reward_percentage' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'unit_selling_price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'is_active' => 'sometimes|boolean',
        ]);
        $product = Product::create($validated);

        return new ProductResource($product);
    }

    // Display a specific product
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    // Update the specified product
    public function update(Request $request, Product $product)
    {
        if (!$request->user()->can('update', $product)) {
            return response()->json([
                'message' => 'You do not have permission to update this product.',
            ], 403);
        }
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'referral_reward_amount' => 'nullable|numeric|min:0',
            'referral_reward_percentage' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'unit_selling_price' => 'sometimes|required|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ], 200);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Cannot delete this product because it is linked to other records.',
                    'errors' => ['sql' => [$e->getMessage()]],

                ], 422);
            }

            return response()->json([
                'message' => 'An unexpected error occurred.',
                'errors' => ['sql' => [$e->getMessage()]],
            ], 500);
        }
    }


    // Deactivate a product
    public function deactivate(Product $product)
    {
        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Product deactivated successfully'], 200);
    }

    // Get product performance details
    public function performance(Product $product)
    {
        $performanceData = [
            'sales' => $product->sales()->sum('quantity'),
            'revenue' => $product->sales()->sum('total_price'),
        ];

        return response()->json([
            'product' => new ProductResource($product),
            'performance' => $performanceData,
        ]);
    }
}
