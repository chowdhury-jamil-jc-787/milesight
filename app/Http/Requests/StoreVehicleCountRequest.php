<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'max:100'],
            'device_name' => ['required', 'string', 'max:150'],
            'mac_address' => ['required', 'string', 'max:50'],
            'sn' => ['nullable', 'string', 'max:100'],
            'time' => ['required', 'date'],

            'detection_region' => ['nullable', 'string', 'max:50'],
            'detection_region_name' => ['required', 'string', 'max:100'],

            'total_vehicle' => ['nullable', 'integer', 'min:0'],
            'count_type_car' => ['nullable', 'integer', 'min:0'],
            'count_type_motorcycle' => ['nullable', 'integer', 'min:0'],
            'count_type_non_motor' => ['nullable', 'integer', 'min:0'],
            'count_type_small_vehicle' => ['nullable', 'integer', 'min:0'],
            'count_type_medium_vehicle' => ['nullable', 'integer', 'min:0'],
            'count_type_large_vehicle' => ['nullable', 'integer', 'min:0'],

            'car' => ['nullable', 'integer', 'min:0'],
            'motorbike' => ['nullable', 'integer', 'min:0'],
            'bus' => ['nullable', 'integer', 'min:0'],
            'truck' => ['nullable', 'integer', 'min:0'],
            'van' => ['nullable', 'integer', 'min:0'],
            'suv' => ['nullable', 'integer', 'min:0'],
            'fire_engine' => ['nullable', 'integer', 'min:0'],
            'ambulance' => ['nullable', 'integer', 'min:0'],
            'bicycle' => ['nullable', 'integer', 'min:0'],
            'other' => ['nullable', 'integer', 'min:0'],

            'full_snapshot' => ['nullable', 'string'],

            '_camera' => ['nullable', 'string', 'max:100'],
            '_camera_mac' => ['nullable', 'string', 'max:50'],
            '_camera_identifier' => ['nullable', 'string', 'max:100'],
            '_local_lane' => ['nullable', 'integer', 'min:0'],
            '_global_lane' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $numericFields = [
            'total_vehicle',
            'count_type_car',
            'count_type_motorcycle',
            'count_type_non_motor',
            'count_type_small_vehicle',
            'count_type_medium_vehicle',
            'count_type_large_vehicle',
            'car',
            'motorbike',
            'bus',
            'truck',
            'van',
            'suv',
            'fire_engine',
            'ambulance',
            'bicycle',
            'other',
            '_local_lane',
            '_global_lane',
        ];

        $data = [];

        foreach ($numericFields as $field) {
            if ($this->has($field)) {
                $data[$field] = is_numeric($this->$field) ? (int) $this->$field : 0;
            }
        }

        $this->merge($data);
    }
}