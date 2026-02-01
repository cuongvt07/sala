<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'phone', 'identity_id', 'birthday', 'nationality'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
