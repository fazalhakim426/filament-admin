<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Trait\CustomRespone;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    use AuthorizesRequests;
    use CustomRespone;
    
    public function index()
    { 
         $params = request()->only(['search', 'sponsor', 'name', 'sku', 'description', 'manzil_choice', 'stock_quantity', 'unit_selling_price', 'category_id', 'sub_category_id', 'is_active', 'orderBy', 'per_page', 'trending', 'sort_by', 'sort_order']);

        $query = Product::where('is_active', true);
    
        // Apply filters dynamically
        if (isset($params['manzil_choice'])) {
            $manzilChoice = $params['manzil_choice'];
            $query->where('manzil_choice', $manzilChoice == "1" ? 1 : 0);
        }
    
        if (isset($params['sponsor'])) {
            $sponsor = $params['sponsor'];
            $query->where('sponsor', $sponsor == "1" ? 1 : 0);
        }
    
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subCategory', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }
    
        if (!empty($params['sku'])) {
            $query->where('sku', 'like', "%{$params['sku']}%");
        }
    
        if (!empty($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }
    
        if (!empty($params['sub_category_id'])) {
            $query->where('sub_category_id', $params['sub_category_id']);
        } 

        if (isset($params['trending']) && $params['trending'] == '1') {
            $query->withAvg('reviews', 'rating_stars')->orderByDesc('reviews_avg_rating_stars');
            
        } elseif (isset($params['sort_by']) && in_array($params['sort_by'], ['price','unit_selling_price','name', 'created_at'])) {
            $sortOrder = $params['sort_order'] ?? 'asc';
            if($params['sort_by']=="price"){
                $params['sort_by'] = "unit_selling_price";
            }
            $query->orderBy($params['sort_by'], $sortOrder);
        }
        $query->where('is_active', isset($params['is_active']) && $params['is_active'] == '0' ? false : true);
        $perPage = $params['per_page'] ?? 10;
        return $this->json(200,true,'Product lists',ProductResource::collection($query->paginate($perPage)));
    }
}