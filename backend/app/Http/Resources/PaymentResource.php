<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk memformat data transaksi Pembayaran (Payment).
 * Mengirimkan metode pembayaran (seperti Xendit), status, dan tanggal pembayaran.
 */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'status' => $this->status,
            'paid_at' => $this->paid_at,
        ];
    }
}
