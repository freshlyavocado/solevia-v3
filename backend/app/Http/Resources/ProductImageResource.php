<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk memformat data Gambar Produk.
 * Mengembalikan path/URL dari gambar agar bisa dirender di antarmuka pengguna.
 */
class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
        ];
    }
}
