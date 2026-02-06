<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRecordRequest;
use App\Http\Requests\UpdateVehicleRecordRequest;
use App\Models\VehicleRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class VehicleRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = VehicleRecord::query();

        // Filters (exact match)
        if ($request->filled('serial')) {
            $q->where('serial', $request->string('serial'));
        }

        if ($request->filled('device_name')) {
            $q->where('device_name', $request->string('device_name'));
        }

        if ($request->filled('license_plate_no')) {
            $q->where('license_plate_no', $request->string('license_plate_no'));
        }

        // vehicle_height filter (exact numeric)
        if ($request->filled('vehicle_height')) {
            $q->where('vehicle_height', (float) $request->input('vehicle_height'));
        }

        $perPage = (int) $request->input('per_page', 20);

        $page = $q->latest()->paginate($perPage);

        return response()->json([
            'count' => $page->total(),
            'previous' => $page->previousPageUrl(),
            'next' => $page->nextPageUrl(),
            'results' => collect($page->items())->map(fn ($record) => $this->formatRecord($record))->values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRecordRequest $request)
    {
        $data = $request->validated();

        if (!empty($data['vehicle_snapshot'])) {
            $data['vehicle_snapshot'] = $this->storeBase64Image($data['vehicle_snapshot']);
        }

        $record = VehicleRecord::create($data);

        return response()->json([
            'message' => 'Created successfully',
            'results' => $this->formatRecord($record),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleRecord $vehicleRecord)
    {
        return response()->json([
            'results' => $this->formatRecord($vehicleRecord),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRecordRequest $request, VehicleRecord $vehicleRecord)
    {
        $data = $request->validated();

        if (!empty($data['vehicle_snapshot'])) {
            if ($vehicleRecord->vehicle_snapshot) {
                Storage::disk('public')->delete($vehicleRecord->vehicle_snapshot);
            }
            $data['vehicle_snapshot'] = $this->storeBase64Image($data['vehicle_snapshot']);
        }

        $vehicleRecord->update($data);

        return response()->json([
            'message' => 'Updated successfully',
            'results' => $this->formatRecord($vehicleRecord),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleRecord $vehicleRecord)
    {
        if ($vehicleRecord->vehicle_snapshot) {
            Storage::disk('public')->delete($vehicleRecord->vehicle_snapshot);
        }

        $vehicleRecord->delete();

        return response()->json([
            'message' => 'Deleted successfully',
        ]);
    }


    private function formatRecord(VehicleRecord $record): array
    {
        return [
            'id' => $record->id,
            'serial' => $record->serial,
            'device_name' => $record->device_name,
            'captured_at' => $record->captured_at,
            'license_plate_no' => $record->license_plate_no,
            'vehicle_height' => $record->vehicle_height,
            'vehicle_snapshot_url' => $record->vehicle_snapshot
                ? asset('storage/' . $record->vehicle_snapshot)
                : null,
            'vehicle_snapshot_path' => $record->vehicle_snapshot,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ];
    }

    private function storeBase64Image(string $base64): string
    {
        // supports: "data:image/png;base64,AAAA..." OR raw base64
        if (str_contains($base64, 'base64,')) {
            [$meta, $content] = explode('base64,', $base64, 2);

            // try get extension from mime
            preg_match('/data:image\/(\w+);/i', $meta, $m);
            $ext = $m[1] ?? 'jpg';
        } else {
            $content = $base64;
            $ext = 'jpg';
        }

        $binary = base64_decode($content, true);
        if ($binary === false) {
            abort(422, 'Invalid base64 image');
        }

        // normalize common ext names
        $ext = strtolower($ext);
        if ($ext === 'jpeg') $ext = 'jpg';

        $filename = 'vehicle_snapshots/' . Str::uuid() . '.' . $ext;

        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }
}
