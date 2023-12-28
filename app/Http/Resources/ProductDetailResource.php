<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends ProductResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request),
        [
            'description' => $this->description,
            'article' => $this->article,
            'galleries' => $this->galleries()->select('thumbnail','sort','product_id')
                            ->orderBy('sort','asc')->get(),
            'brand' => $this->brand()->select('id', 'name')->first(),
            'category' => $this->category()->select('id', 'name')->first()
        ]);
    }
}
