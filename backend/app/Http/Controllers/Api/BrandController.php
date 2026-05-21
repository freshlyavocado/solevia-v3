<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

/**
 * BrandController
 * 
 * Menangani pengambilan data Merek (Brand) sepatu yang tersedia di toko.
 * Endpoint ini bersifat publik (dapat diakses tanpa login) untuk ditampilkan pada etalase halaman utama.
 */
class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->get();
        return BrandResource::collection($brands);
    }

    public function show($id)
    {
        $brand = Brand::withCount('products')->findOrFail($id);
        return new BrandResource($brand);
    }
}
