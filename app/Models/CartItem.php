<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CartItem extends Model
{
    use HasFactory;

    protected static function booted() : void {
        static::addGlobalScope('visible', function(Builder $query) {
            // $query->whereHas('product', function($query) {
            //     $query->where('is_visible',1);
            // });
            $query->has('product');
        });
    }

    protected $table = 'cart_item';
    public $timestamps = false;
    protected $attributes = [
        'quantity' => 1
    ];
    protected $fillable = [
        'quantity', 'total', 'savings', 'cart_id', 'product_id'
    ];
    protected $casts = [
        'quantity' => 'integer',
        'total' => 'float',
        'savings' => 'float',
    ];

    public function cart() : BelongsTo {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
