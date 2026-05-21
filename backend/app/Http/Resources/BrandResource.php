<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk memformat data Merek (Brand).
 * Mengubah data dari model Brand menjadi struktur JSON yang bersih untuk dikonsumsi frontend.
 */
class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_url' => $this->logo_url,
            'description' => $this->description,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
