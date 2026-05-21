<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk memformat data rincian barang dalam satu pesanan (Order Item).
 * Termasuk harga saat dibeli, kuantitas, serta informasi varian dan produk terkait.
 */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant' => new ProductVariantResource($this->whenLoaded('variant')),
            'product' => $this->relationLoaded('variant') && $this->variant->relationLoaded('product') ? new ProductResource($this->variant->product) : null,
            'quantity' => $this->quantity,
            'item_price' => $this->item_price,
        ];
    }
}
