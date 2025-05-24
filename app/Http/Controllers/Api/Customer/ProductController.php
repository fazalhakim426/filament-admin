<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Trait\CustomRespone;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    use CustomRespone;

    public function index()
    {
        $params = request()->only([
            'search',
            'sponsor',
            'name',
            'sku',
            'description',
            'manzil_choice',
            'stock_quantity',
            'unit_selling_price',
            'category_id',
            'sub_category_id',
            'is_active',
            'orderBy',
            'per_page',
            'trending',
            'sort_by',
            'sort_order',
            'supplier_user_id'

        ]);

        $query = Product::with(['productVariants.images'])->where('is_active', true);

        // Filters (same as before) ...
        if (isset($params['manzil_choice'])) {
            $query->where('manzil_choice', $params['manzil_choice'] == "1" ? 1 : 0);
        }
        if (isset($params['supplier_user_id'])) {
            $query->where('supplier_user_id', $params['supplier_user_id']);
        }

        if (isset($params['sponsor'])) {
            $query->where('sponsor', $params['sponsor'] == "1" ? 1 : 0);
        }  
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    })
                    ->orWhereHas('productVariants', function ($query) use ($search) {
                        $query->where('sku', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('variantOptions', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('subCategory', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($params['sku'])) {
            $query->whereHas('productVariants', function ($q) use ($params) {
                $q->where('sku', 'like', "%{$params['sku']}%");
            });
        }

        if (!empty($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }

        if (!empty($params['sub_category_id'])) {
            $query->where('sub_category_id', $params['sub_category_id']);
        }

        if (!empty($params['unit_selling_price'])) {
            $query->whereHas('productVariants', function ($q) use ($params) {
                $q->where('unit_selling_price', $params['unit_selling_price']);
            });
        }

        if (!empty($params['stock_quantity'])) {
            $query->whereHas('productVariants', function ($q) use ($params) {
                $q->where('stock_quantity', '>=', $params['stock_quantity']);
            });
        }

        if (isset($params['trending']) && $params['trending'] == '1') {
            $query->withAvg('reviews', 'rating_stars')->orderByDesc('reviews_avg_rating_stars');
        }

        // 🛠 Now for Sorting
        if (isset($params['sort_by']) && in_array($params['sort_by'], ['price', 'unit_selling_price', 'name', 'created_at'])) {
            $sortOrder = $params['sort_order'] ?? 'asc';

            if ($params['sort_by'] == 'price' || $params['sort_by'] == 'unit_selling_price') { 
                $query->withMin('productVariants', 'unit_selling_price');

                // Then order based on that
                $query->orderBy('product_variants_min_unit_selling_price', $sortOrder);
            } else {
                $query->orderBy($params['sort_by'], $sortOrder);
            }
        }

        $query->where('is_active', isset($params['is_active']) && $params['is_active'] == '0' ? false : true);

        $perPage = $params['per_page'] ?? 10;

        return response()->json([
            'success' => true,
            'message' => 'Product lists',
            'data' => ProductResource::collection($query->paginate($perPage))->response()->getData(true)
        ]);
    }


    function show(Product $product)
    {
        
        $product->load([
            'reviews' => function ($query){
                return $query->latest()->take(5);
            },
            'productVariants.images',
            'supplierUser',
            'category',
            'subCategory',
            'productVariants.variantOptions',
            'reviews.user'
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Product details',
            'data' =>  new ProductResource($product),
                    
        ]);
    }
}
