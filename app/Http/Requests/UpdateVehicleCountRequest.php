<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['sometimes', 'string', 'max:100'],
            'device_name' => ['sometimes', 'string', 'max:150'],
            'mac_address' => ['sometimes', 'string', 'max:50'],
            'sn' => ['nullable', 'string', 'max:100'],
            'time' => ['sometimes', 'date'],

            'detection_region' => ['nullable', 'string', 'max:50'],
            'detection_region_name' => ['sometimes', 'string', 'max:100'],

            'total_vehicle' => ['sometimes', 'integer', 'min:0'],
            'count_type_car' => ['sometimes', 'integer', 'min:0'],
            'count_type_motorcycle' => ['sometimes', 'integer', 'min:0'],
            'count_type_non_motor' => ['sometimes', 'integer', 'min:0'],
            'count_type_small_vehicle' => ['sometimes', 'integer', 'min:0'],
            'count_type_medium_vehicle' => ['sometimes', 'integer', 'min:0'],
            'count_type_large_vehicle' => ['sometimes', 'integer', 'min:0'],

            'car' => ['sometimes', 'integer', 'min:0'],
            'motorbike' => ['sometimes', 'integer', 'min:0'],
            'bus' => ['sometimes', 'integer', 'min:0'],
            'truck' => ['sometimes', 'integer', 'min:0'],
            'van' => ['sometimes', 'integer', 'min:0'],
            'suv' => ['sometimes', 'integer', 'min:0'],
            'fire_engine' => ['sometimes', 'integer', 'min:0'],
            'ambulance' => ['sometimes', 'integer', 'min:0'],
            'bicycle' => ['sometimes', 'integer', 'min:0'],
            'other' => ['sometimes', 'integer', 'min:0'],

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