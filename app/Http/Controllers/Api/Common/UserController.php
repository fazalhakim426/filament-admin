<?php

namespace App\Http\Controllers\Api\Common;

use App\Models\Product;
use App\Http\Controllers\Controller; 
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserResource;
use App\Models\Review;
use App\Models\User;
use App\Trait\CustomRespone;  
class UserController extends Controller
{
    use CustomRespone;
    public function index()
    {
        $params = request()->only(['per_page']);
        $query = User::query(); 
        $perPage = $params['per_page'] ?? 10;
        return response()->json([
            'success' => true,
            'message' => 'User lists',
            'data' => UserResource::collection($query->paginate($perPage))->response()->getData(true)
        ]);
    }
    public function show(User $user)
    { 
        return response()->json([
            'success' => true,
            'message' => 'User detail list',
            'data' => new UserResource($user)
        ]);
    }
}