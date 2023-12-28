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
            'discount_percent' => $this->getSalePercent(),
            'discount_price' => $this->getSalePrice(),
            'canonical' => $this->canonical,
            'quantity' => $this->quantity,
            'remain' => ($this->sale_item) ? $this->sale_item->remain : null,
            'rating' => $this->rating,
            'category_name' => $this->category->name,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
        ];
    }
}
