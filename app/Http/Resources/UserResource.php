<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'cartId' => $this->cart->id,
            'email' => $this->email,
            'fullname' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'isLoggedIn' => true
        ];
    }
}
