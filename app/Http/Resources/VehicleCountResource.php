<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'device_name' => $this->device_name,
            'mac_address' => $this->mac_address,
            'sn' => $this->sn,
            'time' => $this->event_time?->format('Y-m-d H:i:s'),

            'detection_region' => $this->detection_region,
            'detection_region_name' => $this->detection_region_name,

            'total_vehicle' => $this->total_vehicle,
            'count_type_car' => $this->count_type_car,
            'count_type_motorcycle' => $this->count_type_motorcycle,
            'count_type_non_motor' => $this->count_type_non_motor,
            'count_type_small_vehicle' => $this->count_type_small_vehicle,
            'count_type_medium_vehicle' => $this->count_type_medium_vehicle,
            'count_type_large_vehicle' => $this->count_type_large_vehicle,

            'car' => $this->car,
            'motorbike' => $this->motorbike,
            'bus' => $this->bus,
            'truck' => $this->truck,
            'van' => $this->van,
            'suv' => $this->suv,
            'fire_engine' => $this->fire_engine,
            'ambulance' => $this->ambulance,
            'bicycle' => $this->bicycle,
            'other' => $this->other,

            'full_snapshot_url' => $this->full_snapshot_path
                ? Storage::disk('public')->url($this->full_snapshot_path)
                : null,

            '_camera' => $this->_camera,
            '_camera_mac' => $this->_camera_mac,
            '_camera_identifier' => $this->_camera_identifier,
            '_local_lane' => $this->_local_lane,
            '_global_lane' => $this->_global_lane,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}