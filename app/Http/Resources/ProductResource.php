<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public static $wrap = null;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'thumbnail' => $this->thumbnail,
            'name' => $this->name,
            'reg_price' => $this->reg_price,
            'discount_percent' => $this->discount_percent,
            'discount_price' => $this->discount_price,
            'canonical' => $this->canonical,
            'quantity' => $this->quantity,
            'rating' => $this->rating,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id
        ];
    }
}
