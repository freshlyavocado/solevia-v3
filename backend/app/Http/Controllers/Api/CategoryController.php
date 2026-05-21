<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * CategoryController
 * 
 * Menangani pengambilan data Kategori Produk (seperti Sneakers, Running, Formal, dll).
 * Endpoint ini bersifat publik dan berguna untuk keperluan filter produk di frontend.
 */
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        return CategoryResource::collection($categories);
    }

    public function show($id)
    {
        $category = Category::withCount('products')->findOrFail($id);
        return new CategoryResource($category);
    }
}
