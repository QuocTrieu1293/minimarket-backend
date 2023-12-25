<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = '_order';
    public $timestamps = true;
    protected $attributes = [
        'status' => 'pending'
    ];
    protected $fillable = [
        'address', 'total', 'note', 'payment_method', 'user_id'
    ];

    public function user() : BelongsTo {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function order_items() : HasMany {
        return $this->hasMany(OrderItem::class,'order_id','id');
    }
}
