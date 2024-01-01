<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'itemId' => $this->id,
            'productId' => $this->product_id,
            'name' => (($this->from_event)?'[SỰ KIỆN KM] ':'') . $this->product->name,
            'thumbnail' => $this->product->thumbnail,
            'quantity' => $this->quantity,
            'total' => $this->total_price,
            'unit_price' => $this->unit_price
        ];
    }
}
