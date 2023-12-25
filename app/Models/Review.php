<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;
    protected $table = 'review';
    public $timestamps = true;
    protected $fillable = [
        'rating', 'title', 'comment', 'user_id', 'product_id'
    ];

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class,'product_id','id');
    }
 
    public function user() : BelongsTo {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
