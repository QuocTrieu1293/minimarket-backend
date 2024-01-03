<?php

namespace App\Models\Filament;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends User
{
    use HasFactory;
    
    protected $table = 'users';
    protected static function booted() : void
    {
        static::addGlobalScope('customer', function($query) {
            $query->role('customer');
        });
    }


}
