<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleEvent extends Model
{
    use HasFactory;

    protected static function booted() {
        static::addGlobalScope('visible', function(Builder $query) : void {
            $query->where('is_visible', 1);
        });
    }

    protected $table = 'sale_event';
    public $timestamps = true;
    protected $fillable = ['name', 'description', 'start_time', 'end_time', 'is_visible'];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function sale_items() : HasMany {
        return $this->hasMany(SaleItem::class,'event_id','id');
    }
}
