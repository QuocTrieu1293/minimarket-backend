<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = '_order';
    public $timestamps = true;
    protected $attributes = [
        'status' => 'pending',
        'total' => 0,
    ];
    protected $fillable = [
        'address', 'total', 'note', 'payment_method', 'user_id', 'status'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'total' => 'float',
    ];

    public function user() : BelongsTo {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function order_items() : HasMany {
        return $this->hasMany(OrderItem::class,'order_id','id');
    }
}
