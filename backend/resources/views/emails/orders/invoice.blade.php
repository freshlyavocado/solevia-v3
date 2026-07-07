<x-mail::message>
# Invoice Pesanan Anda

Halo **{{ $order->user->name }}**,

Terima kasih telah berbelanja di Solevia! Pesanan Anda dengan nomor **#{{ $order->order_number }}** telah kami catat.

Berikut adalah rincian pesanan Anda:

<x-mail::table>
| Produk | Ukuran | Qty | Harga Satuan |
| :--------- | :--------- | :--- | :----------- |
@foreach($order->items as $item)
| {{ $item->variant->product->name ?? 'Produk' }} | {{ $item->variant->size ?? '-' }} | {{ $item->quantity }} | Rp {{ number_format($item->item_price, 0, ',', '.') }} |
@endforeach
</x-mail::table>

**Total Pembayaran:** Rp {{ number_format($order->total_amount, 0, ',', '.') }}  
**Metode Pembayaran:** {{ strtoupper($order->payment->payment_method ?? 'Transfer') }}

<x-mail::button :url="'http://localhost:5173/orders/' . $order->id">
Lihat Detail Pesanan
</x-mail::button>

Terima kasih atas kepercayaannya,<br>
{{ config('app.name') }}
</x-mail::message>
