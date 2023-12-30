<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleItem extends Model
{
    use HasFactory;

    protected static function booted() {
        static::addGlobalScope('visible', function(Builder $query) : void {
            $query->has('product');
        });
    }

    protected $table = 'sale_item';
    public $timestamps = false;
    protected $attributes = ['quantity' => 1, 'event_id' => 1, 'remain' => 0];
    protected $fillable = [
        'product_id', 'event_id', 'quantity', 'remain'
    ];
    protected $casts = [
        'quantity' => 'integer',
        'remain' => 'integer'
    ];

    public function sale_event() : BelongsTo {
        return $this->belongsTo(SaleEvent::class,'event_id','id');
    }

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
