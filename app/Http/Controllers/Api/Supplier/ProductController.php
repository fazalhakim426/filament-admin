<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
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

        return $this->json(200, true, 'Product list', ProductResource::collection($query->with([
            'productVariants.variantOptions','reviews'
        ])->get()));
    }

    public function show(Product $product)
    {
        return $this->json(200, true, 'Show product', new ProductResource($product->load(
            'productVariants.variantOptions','reviews'
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
            'variants.*.media' => 'required|array',
            'variants.*.media.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,avi,mov,webm|max:20480', 
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
                'description' => $validated['description']??'',
                'supplier_user_id' => Auth::id()
            ]);

            // Create variants if provided
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $index => $variantData) {

                    $variant = $product->productVariants()->create([
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
                    if ($request->hasFile("variants.$index.media")) {
                        foreach ($request->file("variants.$index.media") as $mediaFile) {
                            $filePath = $mediaFile->store('variant_media', 'public');
                            $type = str_starts_with($mediaFile->getMimeType(), 'video') ? 'video' : 'image';

                            $variant->images()->create([
                                'url' => $filePath,
                                'type' => $type,
                            ]);
                        }
                    }
                    
                }
            }


            DB::commit();
            return $this->json(200, true, 'Product created successfully', new ProductResource($product->load('productVariants.variantOptions')));
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
            'variants.*.media' => 'array',
            'variants.*.media.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,avi,mov,webm|max:20480', 
            'variants.*.sku' => [
                'required', 'string', 'max:255',
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
            'variants.*.delete_media_ids' => 'array',
            'variants.*.options.*.id' => 'nullable|exists:variant_options,id',
            'variants.*.options.*.attribute_name' => 'required|string|max:255',
            'variants.*.options.*.attribute_value' => 'required|string|max:255',
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
                'description' => $validated['description']??'',
            ]);

            // Handle Variants
            if (!empty($validated['variants'])) {
                $existingVariantIds = $product->productVariants()->pluck('id')->toArray();
                $updatedVariantIds = [];

                foreach ($validated['variants'] as $index=>$variantData) {

                    if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                        // Update existing variant
                     
                        $variant = ProductVariant::find($variantData['id']);
                        $variant->update([
                            'sku' => $variantData['sku'],
                            'unit_selling_price' => $variantData['unit_selling_price'],
                            'description' => $variantData['description']??'',

                        ]);
                        if (!empty($variantData['delete_media_ids'])) {
                            foreach ($variantData['delete_media_ids'] as $mediaId) { 
                                $media = $variant->images()->find($mediaId);
                                if ($media) {
                                    Storage::disk('public')->delete($media->url);
                                    $media->delete();
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

                    if ($request->hasFile("variants.$index.media")) {
                        foreach ($request->file("variants.$index.media") as $mediaFile) {
                            $filePath = $mediaFile->store('variant_media', 'public');
                            $type = str_starts_with($mediaFile->getMimeType(), 'video') ? 'video' : 'image';
                    
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

            DB::commit();
            return $this->json(200, true, 'Product updated successfully!', new ProductResource($product->load('productVariants.variantOptions')));
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

        return $this->json(200, true, 'Product deactivated successfully',new ProductResource($product->load('productVariants.variantOptions')));
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
