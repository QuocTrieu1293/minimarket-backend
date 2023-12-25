<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sale_event';
    public $timestamps = true;
    protected $fillable = ['name', 'start_time', 'end_time'];

    public function sale_items() : HasMany {
        return $this->hasMany(SaleItem::class,'event_id','id');
    }
}
