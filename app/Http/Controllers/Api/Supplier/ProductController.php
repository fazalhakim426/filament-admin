<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SupplierProductResource;
use App\Models\ProductVariant;
use App\Models\SubCategory;
use App\Models\VariantOption;
use App\Trait\CustomRespone;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    use CustomRespone;
    use AuthorizesRequests;

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
        $products = $query
            ->with([
                'productVariants'
            ])
            ->paginate(request('per_page', 15));

        return $this->json(200, true, 'Products retrieved successfully.', [
            'data' => SupplierProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ]
        ]);
    }

    public function show(Product $product)
    {
        return $this->json(200, true, 'Show product', new SupplierProductResource($product->load(
            'productVariants.variantOptions',
        )));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required|string|max:255|unique:product_variants,sku',
            'variants.*.description' => 'nullable|string',
            'variants.*.discount' => 'nullable|integer|min:0|max:100',
            'variants.*.discount_description' => 'nullable|string',
            'variants.*.images' => 'required|array',
            'variants.*.images.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,avi,mov,webm|max:20480',
            'variants.*.unit_selling_price' => 'required|numeric|min:0',
            'variants.*.options' => 'nullable|array',
            'variants.*.options.*.attribute_name' => 'required|string|max:255',
            'variants.*.options.*.attribute_value' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Assign the supplier
            $validated['supplier_user_id'] = Auth::id();

            // Find the category based on sub-category
            $subCategory = SubCategory::findOrFail($validated['sub_category_id']);
            $validated['category_id'] = $subCategory->category_id;

            // Create the main product
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'supplier_user_id' => Auth::id()
            ]);

            // Create variants if provided
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $index => $variantData) {

                    $variant = $product->productVariants()->create([
                        'sku' => $variantData['sku'],
                        'discount' => $variantData['discount'] ?? 0,
                        'discount_description' => $variantData['discount_description'],
                        'sku' => $variantData['sku'],
                        'unit_selling_price' => $variantData['unit_selling_price'],
                        'description' => $variantData['description'] ?? '',
                    ]);

                    // Create variant options if provided
                    if (!empty($variantData['options'])) {
                        foreach ($variantData['options'] as $option) {
                            $variant->variantOptions()->create([
                                'attribute_name' => $option['attribute_name'],
                                'attribute_value' => $option['attribute_value'],
                            ]);
                        }
                    }
                    // ✅ Handle uploaded images/videos — move this inside the loop
                    if ($request->hasFile("variants.$index.images")) {
                        foreach ($request->file("variants.$index.images") as $imagesFile) {
                            $filePath = $imagesFile->store('variant_images', 'public');
                            $type = str_starts_with($imagesFile->getMimeType(), 'video') ? 'video' : 'image';

                            $variant->images()->create([
                                'url' => $filePath,
                                'type' => $type,
                            ]);
                        }
                    }
                }
            }


            DB::commit();
            return $this->json(200, true, 'Product created successfully', new SupplierProductResource($product->load('productVariants.variantOptions')));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->json(500, false, 'Error: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'variants' => 'required|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.description' => 'nullable|string',
            'variants.*.images' => 'array',
            'variants.*.images.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,avi,mov,webm|max:20480',
            'variants.*.sku' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $variantId = data_get($request->variants, str_replace('variants.', '', explode('.', $attribute)[1]) . '.id');
                    if (!$variantId || !ProductVariant::where('id', '!=', $variantId)->where('sku', $value)->exists()) {
                        return;
                    }
                    $fail('The ' . $attribute . ' has already been taken.');
                }
            ],
            'variants.*.unit_selling_price' => 'required|numeric|min:0',
            'variants.*.options' => 'nullable|array',
            'variants.*.delete_images_ids' => 'array',
            'variants.*.options.*.id' => 'nullable|exists:variant_options,id',
            'variants.*.options.*.attribute_name' => 'required|string|max:255',
            'variants.*.options.*.attribute_value' => 'required|string|max:255',

            'specification.*' => 'nullable|array',
            'specification.*.key' => 'required|string|max:255',
            'specification.*.value' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Update main product details
            $subCategory = SubCategory::findOrFail($validated['sub_category_id']);
            $validated['category_id'] = $subCategory->category_id;
            $product->update([
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
            ]);

            // Handle Variants
            if (!empty($validated['variants'])) {
                $existingVariantIds = $product->productVariants()->pluck('id')->toArray();
                $updatedVariantIds = [];

                foreach ($validated['variants'] as $index => $variantData) {

                    if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                        // Update existing variant

                        $variant = ProductVariant::find($variantData['id']);
                        $variant->update([
                            'sku' => $variantData['sku'],
                            'unit_selling_price' => $variantData['unit_selling_price'],
                            'description' => $variantData['description'] ?? '',

                        ]);
                        if (!empty($variantData['delete_images_ids'])) {
                            foreach ($variantData['delete_images_ids'] as $imagesId) {
                                $images = $variant->images()->find($imagesId);
                                if ($images) {
                                    Storage::disk('public')->delete($images->url);
                                    $images->delete();
                                }
                            }
                        }
                    } else {
                        // Create new variant
                        $variant = $product->productVariants()->create([
                            'sku' => $variantData['sku'],
                            'unit_selling_price' => $variantData['unit_selling_price'],
                        ]);
                    }

                    if ($request->hasFile("variants.$index.images")) {
                        foreach ($request->file("variants.$index.images") as $imagesFile) {
                            $filePath = $imagesFile->store('variant_images', 'public');
                            $type = str_starts_with($imagesFile->getMimeType(), 'video') ? 'video' : 'image';

                            $variant->images()->create([
                                'url' => $filePath,
                                'type' => $type,
                            ]);
                        }
                    }


                    $updatedVariantIds[] = $variant->id;

                    // Handle Variant Options
                    if (!empty($variantData['options'])) {
                        $existingOptionIds = $variant->variantOptions()->pluck('id')->toArray();
                        $updatedOptionIds = [];

                        foreach ($variantData['options'] as $option) {
                            if (isset($option['id']) && in_array($option['id'], $existingOptionIds)) {
                                // Update existing option
                                $variantOption = VariantOption::find($option['id']);
                                $variantOption->update([
                                    'attribute_name' => $option['attribute_name'],
                                    'attribute_value' => $option['attribute_value'],
                                ]);
                            } else {
                                // Create new option
                                $variantOption = $variant->variantOptions()->create([
                                    'attribute_name' => $option['attribute_name'],
                                    'attribute_value' => $option['attribute_value'],
                                ]);
                            }
                            $updatedOptionIds[] = $variantOption->id;
                        }

                        $variant->variantOptions()->whereNotIn('id', $updatedOptionIds)->delete();
                    }
                }

                $product->productVariants()->whereNotIn('id', $updatedVariantIds)->delete();
            }
            // foreach ($validated['specifications'] as $option) {
            //     $variant->variantOptions()->create([
            //         'key' => strtolower($option['key']), // normalize
            //         'value' => $option['value'],
            //     ]);
            // }

            DB::commit();
            return $this->json(200, true, 'Product updated successfully!', new SupplierProductResource($product->load('productVariants.variantOptions')));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->json(500, false, 'Error: ' . $e->getMessage());
        }
    }




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

    public function deactivate(Product $product)
    {
        $product->update(['is_active' => false]);

        return $this->json(200, true, 'Product deactivated successfully', new SupplierProductResource($product->load('productVariants.variantOptions')));
    }


    // Get product performance details
    public function performance(Product $product)
    {
        $performanceData = [
            'sales' => $product->sales()->sum('quantity'),
            'revenue' => $product->sales()->sum('total_price'),
        ];

        return $this->json(200, true, 'performace data', [
            'product' => new SupplierProductResource($product),
            'performance' => $performanceData,
        ]);
    }
}
