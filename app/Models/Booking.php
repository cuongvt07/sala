<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Booking extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = ['customer_id', 'room_id', 'check_in', 'check_out', 'price_type', 'is_contract', 'unit_price', 'price', 'deposit', 'deposit_2', 'deposit_3', 'deposit_usd', 'deposit_2_usd', 'usd_rate', 'status', 'notes', 'source', 'additional_guests'];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'additional_guests' => 'array',
        'is_contract' => 'boolean',
        'deposit_usd' => 'float',
        'deposit_2_usd' => 'float',
        'usd_rate' => 'float',
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
