<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $fillable = ['area_id', 'code', 'type', 'price_day', 'price_hour', 'status', 'description'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
