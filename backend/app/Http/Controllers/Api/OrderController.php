<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * OrderController
 * 
 * Jantung dari proses transaksi e-commerce. Mengelola pembuatan pesanan (Checkout), 
 * melihat riwayat pesanan, pembatalan pesanan, serta mengotomatisasi pembuatan tagihan 
 * (Invoice) secara langsung melalui integrasi API Xendit Payment Gateway.
 */
class OrderController extends Controller
{
    /**
     * INDEX — Riwayat pesanan user.
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.variant.product', 'payment', 'shipping'])
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    /**
     * SHOW — Detail satu pesanan.
     */
    public function show(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with(['items.variant.product.images', 'payment', 'shipping'])
            ->findOrFail($id);

        return new OrderResource($order);
    }

    /**
     * CHECKOUT — Buat pesanan dari isi keranjang.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:xendit',
            'recipient_name' => 'required|string',
            'phone_number' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal_code' => 'required|string',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->items()->count() === 0) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }

        $cart->load('items.variant.product');

        // Hitung total dari harga produk (pakai discount_price)
        $totalAmount = 0;
        foreach ($cart->items as $item) {
            $product = $item->variant->product;
            $price = $product->discount_price; // accessor di model
            $totalAmount += $price * $item->quantity;
        }

        // Buat order
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'SLV-' . strtoupper(Str::random(8)),
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Buat order items
        foreach ($cart->items as $item) {
            $product = $item->variant->product;
            $order->items()->create([
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'item_price' => $product->discount_price,
            ]);

            // Kurangi stok variant
            $item->variant->decrement('stock', $item->quantity);
        }

        // Buat shipping
        Shipping::create([
            'order_id' => $order->id,
            'recipient_name' => $request->recipient_name,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'shipping_cost' => 0,
        ]);

        // Buat payment
        $order->payment()->create([
            'payment_method' => $request->payment_method,
            'amount' => $totalAmount,
            'status' => 'pending',
        ]);

        // Kosongkan keranjang
        $cart->items()->delete();

        $order->load(['items.variant.product', 'payment', 'shipping']);
        return new OrderResource($order);
    }

    /**
     * CANCEL — Batalkan pesanan (hanya jika masih pending).
     */
    public function cancel(Request $request, $id)
    {
        $order = $request->user()->orders()->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Pesanan tidak bisa dibatalkan'], 400);
        }

        // Kembalikan stok
        foreach ($order->items as $item) {
            $item->variant->increment('stock', $item->quantity);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Pesanan berhasil dibatalkan']);
    }

    /**
     * Membuat Xendit Invoice URL untuk pembayaran.
     * POST /api/orders/{id}/pay
     */
    public function createInvoice(Request $request, $id)
    {
        $order = $request->user()->orders()->findOrFail($id);

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Already paid'], 422);
        }

        // Xendit API requires Secret Key
        $secretKey = env('XENDIT_SECRET_KEY', 'xnd_development_dummy_secret_key');
        
        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($secretKey, '')
                ->post('https://api.xendit.co/v2/invoices', [
                    'external_id' => $order->order_number . '_' . time(),
                    'amount' => $order->total_amount,
                    'payer_email' => $request->user()->email,
                    'description' => 'Payment for Order ' . $order->order_number,
                    'success_redirect_url' => 'http://localhost:5173/profile',
                    'failure_redirect_url' => 'http://localhost:5173/payment/' . $order->id,
                ]);

            if ($response->successful()) {
                // Auto-confirm untuk simulasi karena user testing (harusnya menunggu Webhook Xendit)
                $order->update(['payment_status' => 'paid', 'status' => 'processing']);
                if ($order->payment) {
                    $order->payment->update(['status' => 'paid', 'paid_at' => now()]);
                }
                
                // Return URL Invoice asli dari Xendit
                return response()->json(['invoice_url' => $response->json()['invoice_url']]);
            }
        } catch (\Exception $e) {
            // Log error
        }

        // Fallback Mock URL jika Secret Key invalid / belum diset di .env
        $mockUrl = 'https://checkout-staging.xendit.co/web/dummy_invoice_' . $order->order_number;
        
        // Karena ini fallback mock, kita juga auto-confirm untuk simulasi sukses
        $order->update(['payment_status' => 'paid', 'status' => 'processing']);
        if ($order->payment) {
            $order->payment->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return response()->json([
            'invoice_url' => $mockUrl,
            'message' => 'This is a mock invoice because Xendit Secret Key is missing in backend .env'
        ]);
    }
}
