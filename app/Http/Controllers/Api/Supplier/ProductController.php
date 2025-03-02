<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\SubCategory;
use App\Trait\CustomRespone; 
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    use CustomRespone;
    use AuthorizesRequests;
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get a list of products for the authenticated supplier",
     *     description="Retrieve a list of products for the authenticated supplier with optional filters.",
     *     tags={"Supplier Products"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter by product name.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by product name.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Filter by product SKU.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Filter by product description.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="stock_quantity",
     *         in="query",
     *         description="Filter by product stock quantity.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="unit_selling_price",
     *         in="query",
     *         description="Filter by product unit selling price.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by product category ID.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by product activity status (true or false).",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="sku", type="string"),
     *                     @OA\Property(property="is_active", type="boolean"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="stock_quantity", type="integer"),
     *                     @OA\Property(property="unit_selling_price", type="string"),
     *                     @OA\Property(property="referral_reward_type", type="enum", nullable=true),
     *                     @OA\Property(property="referral_reward_value", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string"),
     *                     @OA\Property(property="updated_at", type="string"),
     *                     @OA\Property(
     *                         property="category",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="description", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid parameters.",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, authentication required.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *     )
     * )
     */
    public function index()
    {
        // Retrieve parameters
        $params = request()->only(['search', 'name', 'sku', 'description', 'manzil_choice', 'stock_quantity', 'unit_selling_price', 'category_id', 'is_active']);

        $query = Product::where('supplier_user_id', Auth::id());

        // Apply filters dynamically
        if (!empty($params['search'])) {
            $query->where('name', 'like', "%{$params['search']}%");
        }
        if (!empty($params['description'])) {
            $query->where('description', 'like', "%{$params['description']}%");
        }
        if (!empty($params['unit_selling_price'])) {
            $query->where('unit_selling_price', 'like', "%{$params['unit_selling_price']}%");
        }
        if (!empty($params['referral_reward_value'])) {
            $query->where('referral_reward_value', 'like', "%{$params['referral_reward_value']}%");
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
            $query->where('is_active', $params['is_active'] == 'true' ? true : false);
        }

        return $this->json(200, true, 'Product list', ProductResource::collection($query->get()));
    }
    /**
     * @OA\Get(
     *     path="/supplier/products/{id}",
     *     summary="Display a specific product by its ID",
     *     description="Retrieve detailed information about a specific product by its ID.",
     *     tags={"Supplier Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1, description="The ID of the product")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="animi officia"),
     *                 @OA\Property(property="sku", type="string", example="Piuuo"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="description", type="string", example="Dolor nemo velit et voluptatem numquam voluptas."),
     *                 @OA\Property(property="stock_quantity", type="integer", example=104),
     *                 @OA\Property(property="unit_selling_price", type="string", example="283.54"),
     *                 @OA\Property(property="referral_reward_type", type="enum", example="fixed,percentage"),
     *                 @OA\Property(property="referral_reward_value", type="string", example="null"),
     *                 @OA\Property(property="created_at", type="string", example="12 hours ago"),
     *                 @OA\Property(property="updated_at", type="string", example="12 hours ago"),
     *                 @OA\Property(property="category", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics"),
     *                     @OA\Property(property="description", type="string", example="Devices, gadgets, and accessories.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function show(Product $product)
    {
        return $this->json(200, true, 'Show product', new ProductResource($product));
    }

    /**
     * @OA\Post(
     *     path="/supplier/products",
     *     summary="Store a newly created product",
     *     description="Store a newly created product with the provided data.",
     *     tags={"Supplier Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "name", "stock_quantity", "unit_selling_price"},
     *             @OA\Property(property="category_id", type="integer", example=1, description="The ID of the product's category"),
     *             @OA\Property(property="name", type="string", example="animi officia", description="The name of the product"),
     *             @OA\Property(property="description", type="string", example="Dolor nemo velit et voluptatem numquam voluptas.", description="A description of the product"),
     *             @OA\Property(property="referral_reward_value", type="number", format="float", example=10.00, description="A monetary reward amount"),
     *             @OA\Property(property="referral_reward_type", type="enum", format="string", example=fixed|percentage, description="A percentage of the reward"),
     *             @OA\Property(property="stock_quantity", type="integer", example=100, description="The quantity of the product in stock"),
     *             @OA\Property(property="unit_selling_price", type="number", format="float", example=283.54, description="The selling price of the product"),
     *             @OA\Property(property="sku", type="string", example="Piuuo", description="A unique SKU for the product"),
     *             @OA\Property(property="is_active", type="boolean", example=true, description="Flag to indicate if the product is active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="animi officia"),
     *                 @OA\Property(property="sku", type="string", example="Piuuo"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="description", type="string", example="Dolor nemo velit et voluptatem numquam voluptas."),
     *                 @OA\Property(property="stock_quantity", type="integer", example=100),
     *                 @OA\Property(property="unit_selling_price", type="string", example="283.54"),
     *                  @OA\Property(property="referral_reward_type", type="enum", example=fixed,percentage),
     * 
     *                 @OA\Property(property="referral_reward_value", type="string", example="null"),
     *                 @OA\Property(property="created_at", type="string", example="0 seconds ago"),
     *                 @OA\Property(property="updated_at", type="string", example="0 seconds ago"),
     *                 @OA\Property(property="category", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics"),
     *                     @OA\Property(property="description", type="string", example="Devices, gadgets, and accessories.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="The given data was invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="category_id", type="array", @OA\Items(type="string", example="The selected category id is invalid."))
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'referral_reward_value' => 'nullable|numeric|min:0',
            'referral_reward_type' => 'nullable|in:fixed,percentage',
            'unit_selling_price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'is_active' => 'sometimes|boolean',
        ]);
        $validated['supplier_user_id'] = Auth::id();
        $subCategory = SubCategory::find($validated['sub_category_id']);
        $validated['category_id'] = $subCategory->category_id;
        $product = Product::create($validated);
        return $this->json(200, true, 'Product created successfully', new ProductResource($product));
    }



    /**
     * @OA\Delete(
     *     path="/supplier/products/{id}",
     *     summary="Delete a specific product",
     *     description="Delete a specific product by its ID.",
     *     tags={"Supplier Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product deleted successfully."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete this product because it is linked to other records",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Cannot delete this product because it is linked to other records."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="sql",
     *                     type="array",
     *                     @OA\Items(type="string", example="SQLSTATE[23000]: Integrity constraint violation: ...")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="An unexpected error occurred."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="sql",
     *                     type="array",
     *                     @OA\Items(type="string", example="Error message details")
     *                 )
     *             )
     *         )
     *     )
     * )
     */


    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return $this->json(200, true, 'Product deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->json(422, false, 'Cannot delete this product because it is linked to other records.', [], ['sql' => [$e->getMessage()]]);
            }
            return $this->json(500, false, 'An unexpected database error occurred.', [], ['sql' => [$e->getMessage()]]);
        }
    }



    /**
     * @OA\Put(
     *     path="/supplier/products/{id}",
     *     summary="Update a specific product by its ID",
     *     description="Update the details of a specific product identified by its ID.",
     *     tags={"Supplier Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1, description="The ID of the product")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="description", type="string", example="Updated product description."),
     *             @OA\Property(property="referral_reward_value", type="number", format="float", example=10.00),
     *             @OA\Property(property="referral_reward_type", type="enum",  , example=fixed,percentage),
     *             @OA\Property(property="stock_quantity", type="integer", example=150),
     *             @OA\Property(property="unit_selling_price", type="number", format="float", example=300.00),
     *             @OA\Property(property="sku", type="string", example="UpdatedSKU123"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Product Name"),
     *                 @OA\Property(property="sku", type="string", example="UpdatedSKU123"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="description", type="string", example="Updated product description."),
     *                 @OA\Property(property="stock_quantity", type="integer", example=150),
     *                 @OA\Property(property="unit_selling_price", type="string", example="300.00"),
     *                 @OA\Property(property="referral_reward_type", type="enum", example="fixed|percentage"),
     *                 @OA\Property(property="referral_reward_value", type="string", example="null"),
     *                 @OA\Property(property="created_at", type="string", example="12 hours ago"),
     *                 @OA\Property(property="updated_at", type="string", example="0 seconds ago"),
     *                 @OA\Property(property="category", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics"),
     *                     @OA\Property(property="description", type="string", example="Devices, gadgets, and accessories.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You do not have permission to update this product",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have permission to update this product.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="The given data was invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="category_id", type="array", @OA\Items(type="string", example="The selected category id is invalid."))
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, Product $product)
    {
        // if (!$request->user()->can('update', $product)) {
        //     throw ValidationException::withMessages([
        //         'id' => ['You do not have permission to update this product.'],
        //     ]); 
        // }

        $validated = $request->validate([
            'sub_category_id' => 'required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'referral_reward_value' => 'nullable|numeric|min:0',
            'referral_reward_type' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'unit_selling_price' => 'sometimes|required|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $subCategory = SubCategory::find($validated['sub_category_id']);
        $validated['category_id'] = $subCategory->category_id;
        $product->update($validated);
        return $this->json(200, true, 'Product update successfully!', new ProductResource($product));
    }



    /**
     * @OA\Patch(
     *     path="/supplier/products/{id}/deactivate",
     *     summary="Deactivate a product",
     *     description="Deactivate a specific product by its ID.",
     *     tags={"Supplier Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to deactivate",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deactivated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product deactivated successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product not found"
     *             )
     *         )
     *     )
     * )
     */
    public function deactivate(Product $product)
    {
        $product->update(['is_active' => false]);

        return $this->json(200, true, 'Product deactivated successfully');
    }


    // Get product performance details
    public function performance(Product $product)
    {
        $performanceData = [
            'sales' => $product->sales()->sum('quantity'),
            'revenue' => $product->sales()->sum('total_price'),
        ];

        return $this->json(200, true, 'performace data', [
            'product' => new ProductResource($product),
            'performance' => $performanceData,
        ]);
    }
}
