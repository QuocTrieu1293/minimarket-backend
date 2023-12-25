<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cartItemId' => $this->id,
            'productId' => $this->product_id,
            'thumbnail' => $this->product->thumbnail,
            'name' => $this->product->name,
            'quantity' => $this->quantity,
            'reg_price' => $this->product->reg_price,
            'discount_price' => $this->product->discount_price
        ];
    }
}
