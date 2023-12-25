<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_item';
    public $timestamps = 'false';
    protected $attributes = [
        'quantity' => 1
    ];
    protected $fillable = [
        'quantity', 'total_price', 'savings', 'cart_id', 'product_id'
    ];
}
