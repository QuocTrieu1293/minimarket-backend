<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryGroup extends Model
{
    use HasFactory;

    protected $table = 'category_group';
    public $timestamps = false;
    protected $fillable = ['name', 'thumbnail'];

    public function categories() : HasMany {
        return $this->hasMany(Category::class,'category_group_id','id');
    }
}
