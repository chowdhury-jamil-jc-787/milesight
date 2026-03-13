<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'event_time' => 'datetime',
        'raw_payload' => 'array',

        'total_vehicle' => 'integer',
        'count_type_car' => 'integer',
        'count_type_motorcycle' => 'integer',
        'count_type_non_motor' => 'integer',
        'count_type_small_vehicle' => 'integer',
        'count_type_medium_vehicle' => 'integer',
        'count_type_large_vehicle' => 'integer',

        'car' => 'integer',
        'motorbike' => 'integer',
        'bus' => 'integer',
        'truck' => 'integer',
        'van' => 'integer',
        'suv' => 'integer',
        'fire_engine' => 'integer',
        'ambulance' => 'integer',
        'bicycle' => 'integer',
        'other' => 'integer',

        '_local_lane' => 'integer',
        '_global_lane' => 'integer',
    ];
}
