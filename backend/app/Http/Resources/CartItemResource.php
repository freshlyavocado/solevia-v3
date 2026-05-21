<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk memformat data Item Keranjang.
 * Mengembalikan detail barang, varian, dan produk yang dimasukkan ke dalam keranjang belanja.
 */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant' => new ProductVariantResource($this->whenLoaded('variant')),
            'product' => $this->relationLoaded('variant') && $this->variant->relationLoaded('product') ? new ProductResource($this->variant->product) : null,
            'quantity' => $this->quantity,
        ];
    }
}
