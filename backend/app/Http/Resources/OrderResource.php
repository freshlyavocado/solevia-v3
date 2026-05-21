<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource utama untuk memformat data Pesanan (Order).
 * Menggabungkan informasi dasar pesanan, status pembayaran, item yang dibeli, serta detail pengiriman.
 */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'shipping' => new ShippingResource($this->whenLoaded('shipping')),
            'created_at' => $this->created_at,
        ];
    }
}
