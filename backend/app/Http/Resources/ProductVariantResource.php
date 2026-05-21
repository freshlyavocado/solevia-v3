<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk memformat data Varian Produk.
 * Berisi spesifikasi ukuran (size) dan jumlah stok yang tersisa untuk produk tertentu.
 */
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'stock' => $this->stock,
        ];
    }
}
