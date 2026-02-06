<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'serial' => ['string','max:255'],
            'device_name' => ['required','string','max:255'],
            'captured_at' => ['required','date'], // e.g. 2026-02-05 12:34:56
            'license_plate_no' => ['required','string','max:255'],
            'vehicle_height' => ['nullable','numeric'],
            'vehicle_snapshot' => ['nullable','string'], // base64 string
        ];
    }
}
