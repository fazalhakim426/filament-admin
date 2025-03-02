<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SubCategoryResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Trait\CustomRespone;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use CustomRespone;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->json(200,true,'categories list',CategoryResource::collection(Category::with('subCategory')->get()));
    }
    function subCategories(Category $category) {
        return $this->json(200,true,$category->name.' sub categories list',SubCategoryResource::collection($category->subCategory));
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
