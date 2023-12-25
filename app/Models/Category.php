<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Category extends Model
{
    use HasFactory;
    protected $table = 'category';
    public $timestamps = false;
    protected $fillable = ['name', 'description', 'thumbnail'];
    
    public function products() : HasMany {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function category_group() : BelongsTo {
        return $this->belongsTo(CategoryGroup::class,'category_group_id', 'id');
    }
}
