<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

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
            'remaining' => ($this->sale_item) ? $this->sale_item->remain : null,
            'rating' => $this->rating,
            'category_name' => $this->category->name,
            'category_id' => $this->category_id,    
            'brand_id' => $this->brand_id,
        ];
    }
    public static function collection($resource)
    {
        // dd($resource->items());
        if($resource instanceof LengthAwarePaginator) 
            return [
                'data' => array_map(fn($product) => new ProductResource($product),
                                    (array)$resource->items()
                                ),
                'page' => $resource->lastPage()
            ];
        return $resource->map(fn($product) => new ProductResource($product));
    }
}
