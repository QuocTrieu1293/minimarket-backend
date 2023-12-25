<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gallery extends Model
{
    use HasFactory;
    
    protected $table = 'gallery';
    public $timestamps = false;
    protected $fillable = ['thumbnail', 'sort', 'product_id'];

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    
}
