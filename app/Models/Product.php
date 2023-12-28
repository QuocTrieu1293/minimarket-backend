<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted() : void {
        static::addGlobalScope('visible', function(Builder $query) {
            $query->where('is_visible',1);
        });
    }
    
    protected $table = 'product';
    public $timestamps = true;
    protected $attributes = [
        'rating' => 5.0,
    ]; //quy định giá trị mặc định cho các trường trong bảng
    protected $fillable = [
        'name', 'reg_price', 'discount_price','discount_percent', 'quantity', 'unit', 'canonical', 'description', 'is_featured', 'is_visible', 'article', 'rating'
    ];// để có thể sử dụng ORM, Product::create(data);

    protected $casts = [
        'reg_price' => 'float',
        'discount_price' => 'float',
        'discount_percent' => 'integer',
        'event_price' => 'float',
        'event_percent' => 'integer',
        'quantity' => 'integer',
        'rating' => 'float'
    ];

    //event_price hoặc discount_price 
    public function getSalePrice() : float {
        return $this->event_price ?? $this->discount_price;
    }
    //event_percent hoặc discount_percent
    public function getSalePercent() : int {
        return $this->event_percent ?? $this->discount_percent;
    }

    public function category() : BelongsTo {
        return $this->belongsTo(Category::class,'category_id','id');
    }

    public function brand() : BelongsTo {
        return $this->belongsTo(Brand::class,'brand_id','id');
    }

    public function galleries() : HasMany {
        return $this->hasMany(Gallery::class,'product_id','id');
    }

    public function reviews() : HasMany {
        return $this->hasMany(Review::class,'product_id','id');
    }

    public function sale_item() : HasOne {
        return $this->hasOne(SaleItem::class,'product_id','id');
    }

    public function order_items() : HasMany {
        return $this->hasMany(OrderItem::class,'product_id','id');
    }

    public function cart_items() : HasMany {
        return $this->hasMany(CartItem::class, 'product_id', 'id');
    }
}
