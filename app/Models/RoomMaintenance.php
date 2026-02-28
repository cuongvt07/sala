<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomMaintenance extends Model
{
    protected $fillable = [
        'room_id',
        'maintenance_date',
        'task_name',
        'description',
        'cost',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(\App\Models\Room::class);
    }
}
