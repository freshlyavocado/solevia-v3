<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\Request;

/**
 * WishlistController
 * 
 * Mengelola fitur "Daftar Keinginan" (Wishlist) pengguna.
 * Memungkinkan pengguna menyimpan produk favorit mereka untuk dibeli nanti 
 * dengan mekanisme "toggle" (tambah jika belum ada, hapus jika sudah ada).
 */
class WishlistController extends Controller
{
    /**
     * INDEX — Ambil semua wishlist milik user.
     */
    public function index(Request $request)
    {
        $wishlists = $request->user()
            ->wishlists()
            ->with('product.images', 'product.brand')
            ->latest()
            ->get();

        return WishlistResource::collection($wishlists);
    }

    /**
     * TOGGLE — Tambah/hapus produk dari wishlist.
     *
     * Kalau belum ada → tambahkan.
     * Kalau sudah ada → hapus (toggle).
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();

        $wishlist = $user->wishlists()
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['message' => 'Produk dihapus dari wishlist', 'wishlisted' => false]);
        }

        $user->wishlists()->create(['product_id' => $request->product_id]);
        return response()->json(['message' => 'Produk ditambahkan ke wishlist', 'wishlisted' => true], 201);
    }
}
