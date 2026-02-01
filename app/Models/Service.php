<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'type', 'unit_price', 'unit_name', 'description', 'is_active'];

    protected $casts = [
        'unit_price' => 'decimal:0',
        'is_active' => 'boolean',
    ];
}
