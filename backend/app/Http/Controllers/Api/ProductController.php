<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * ProductController
 * 
 * Pusat kontrol katalog produk Solevia. Menangani pencarian produk, filter (berdasarkan merek/kategori), 
 * serta melihat detail spesifik suatu produk. Seluruh endpoint di sini bersifat publik (tanpa login).
 */
class ProductController extends Controller
{
    /**
     * INDEX — Daftar semua produk.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images', 'variants']);

        // Filter berdasarkan kategori
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter berdasarkan brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Search berdasarkan nama
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(15);

        return ProductResource::collection($products);
    }

    /**
     * SHOW — Detail satu produk berdasarkan slug.
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'brand', 'images', 'variants'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
