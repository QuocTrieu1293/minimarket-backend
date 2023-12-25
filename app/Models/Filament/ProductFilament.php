<?php

namespace App\Models\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductFilament extends Product
{
    protected static function booted() : void {
    }
}
