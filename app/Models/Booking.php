<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Booking extends Model
{
<<<<<<< HEAD
    use HasFactory;
    protected $fillable = ['customer_id', 'room_id', 'check_in', 'check_out', 'price_type', 'unit_price', 'price', 'deposit', 'deposit_2', 'deposit_3', 'status', 'notes', 'source', 'additional_guests'];
=======
    use HasFactory, LogsActivity;
    protected $fillable = ['customer_id', 'room_id', 'check_in', 'check_out', 'price_type', 'unit_price', 'price', 'deposit', 'deposit_2', 'deposit_3', 'status', 'notes', 'source'];
>>>>>>> 916b261e0b1872196341bc0c72d2628fec8e3e7f

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'additional_guests' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot(['quantity', 'start_index', 'end_index', 'usage', 'unit_price', 'total_amount', 'note'])
            ->withTimestamps();
    }

    public function usageLogs()
    {
        return $this->hasMany(BookingUsageLog::class);
    }
}
