<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsRecord extends Model
{
    use HasFactory;

    protected $table = 'gps_records';

    protected $fillable = [
        'license_plate',
        'driver_name',
        'fleet_number',
        'date',
        'time',
        'location',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'date' => 'date',
        // 'time' => 'datetime:H:i:s', // Or just string if stored as time column
        'longitude' => 'decimal:6',
        'latitude' => 'decimal:6',
    ];
}
