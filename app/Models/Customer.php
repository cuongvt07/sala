<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;
use App\Traits\LogsActivity;

class Customer extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = ['name', 'email', 'phone', 'identity_id', 'birthday', 'nationality', 'visa_number', 'visa_expiry', 'images', 'notes'];

    protected $casts = [
        'birthday' => 'date',
        'visa_expiry' => 'date',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
