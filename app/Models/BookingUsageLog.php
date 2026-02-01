<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'service_id',
        'type',
        'billing_unit',
        'start_index',
        'end_index',
        'quantity',
        'unit_price',
        'total_amount',
        'billing_date',
        'notes',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'start_index' => 'decimal:2',
        'end_index' => 'decimal:2',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
