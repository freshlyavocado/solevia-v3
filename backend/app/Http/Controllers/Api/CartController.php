<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

/**
 * CartController
 * 
 * Mengelola keranjang belanja pengguna (Cart).
 * Termasuk melihat isi keranjang, menambahkan produk baru, mengubah kuantitas (jumlah) produk, 
 * hingga menghapus produk tertentu dari keranjang sebelum proses checkout.
 */
class CartController extends Controller
{
    /**
     * INDEX — Lihat isi keranjang user.
     */
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('items.variant.product.images');

        return new CartResource($cart);
    }

    /**
     * ADD ITEM — Tambah variant ke keranjang.
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // Cek apakah variant sudah ada di cart
        $item = $cart->items()->where('variant_id', $request->variant_id)->first();

        if ($item) {
            $item->update(['quantity' => $item->quantity + $request->quantity]);
        } else {
            $cart->items()->create([
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
            ]);
        }

        $cart->load('items.variant.product.images');
        return new CartResource($cart);
    }

    /**
     * UPDATE ITEM — Update quantity item di keranjang.
     */
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => $request->quantity]);

        $cart->load('items.variant.product.images');
        return new CartResource($cart);
    }

    /**
     * REMOVE ITEM — Hapus item dari keranjang.
     */
    public function removeItem(Request $request, $itemId)
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $cart->items()->findOrFail($itemId)->delete();

        $cart->load('items.variant.product.images');
        return new CartResource($cart);
    }
}
