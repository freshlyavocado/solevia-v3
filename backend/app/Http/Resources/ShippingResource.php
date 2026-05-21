<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk memformat data Informasi Pengiriman.
 * Mengemas rincian alamat tujuan pengiriman, nomor telepon, dan nomor resi pengiriman (tracking).
 */
class ShippingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipient_name' => $this->recipient_name,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'shipping_cost' => $this->shipping_cost,
        ];
    }
}
