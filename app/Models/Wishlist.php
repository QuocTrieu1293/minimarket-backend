<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected static function booted() {
        static::addGlobalScope('visible', function(Builder $query) : void {
            $query->has('product');
        });
    }

    protected $table = 'wishlist';
    public $timestamps = false;
    protected $fillable = ['user_id', 'product_id'];

    public function user() : BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
