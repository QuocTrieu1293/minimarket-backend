<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    public $timestamps = false;
    protected $attributes = [
        'quantity' => 0,
        'total' => 0,
        'savings' => 0
    ];
    protected $casts = [
        'quantity' => 'integer',
        'total' => 'float',
        'savings' => 'float'
    ];
    protected $fillable = [
        'quantity', 'total', 'savings', 'user_id'
    ];

    public function cart_items() : HasMany {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    public function user() : BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
