<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sale_item';
    public $timestamps = true;
    protected $attributes = ['quantity' => 1];
    protected $fillable = ['product_id', 'event_id', 'quantity'];

    public function sale_event() : BelongsTo {
        return $this->belongsTo(SaleEvent::class,'event_id','id');
    }

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
