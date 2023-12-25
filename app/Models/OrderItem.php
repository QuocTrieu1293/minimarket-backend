<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $table = 'order_item';
    public $timestamps = false;
    protected $fillable = [
        'unit_price', 'quantity', 'order_id', 'product_id'
    ];

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class,'product_id','id');
    }
    public function order() : BelongsTo {
        return $this->belongsTo(Order::class,'order_id','id');
    }
}
