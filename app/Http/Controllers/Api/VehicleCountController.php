<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleCountRequest;
use App\Http\Requests\UpdateVehicleCountRequest;
use App\Http\Resources\VehicleCountResource;
use App\Models\VehicleCount;
use App\Traits\HandlesBase64Image;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleCountController extends Controller
{
    use HandlesBase64Image;

    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 20);
            $perPage = $perPage > 0 ? min($perPage, 100) : 20;

            $sortableColumns = [
                'id',
                'event_type',
                'device_name',
                'mac_address',
                'sn',
                'event_time',
                'detection_region',
                'detection_region_name',
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
                '_camera',
                '_camera_mac',
                '_camera_identifier',
                '_local_lane',
                '_global_lane',
                'created_at',
                'updated_at',
            ];

            $filterableColumns = [
                'id',
                'event_type',
                'device_name',
                'mac_address',
                'sn',
                'detection_region',
                'detection_region_name',
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
                '_camera',
                '_camera_mac',
                '_camera_identifier',
                '_local_lane',
                '_global_lane',
            ];

            $searchableColumns = [
                'event_type',
                'device_name',
                'mac_address',
                'sn',
                'detection_region',
                'detection_region_name',
                '_camera',
                '_camera_mac',
                '_camera_identifier',
            ];

            $query = VehicleCount::query()
                ->select([
                    'id',
                    'event_type',
                    'device_name',
                    'mac_address',
                    'sn',
                    'event_time',
                    'detection_region',
                    'detection_region_name',
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
                    'full_snapshot_path',
                    '_camera',
                    '_camera_mac',
                    '_camera_identifier',
                    '_local_lane',
                    '_global_lane',
                    'created_at',
                    'updated_at',
                ]);

            /*
            |--------------------------------------------------------------------------
            | Exact filters for all columns
            |--------------------------------------------------------------------------
            */
            foreach ($filterableColumns as $column) {
                if ($request->filled($column)) {
                    $query->where($column, $request->get($column));
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Date range filter
            |--------------------------------------------------------------------------
            */
            if ($request->filled('date_from')) {
                $query->where('event_time', '>=', $request->get('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->where('event_time', '<=', $request->get('date_to'));
            }

            /*
            |--------------------------------------------------------------------------
            | Global search
            |--------------------------------------------------------------------------
            */
            if ($request->filled('search')) {
                $search = trim($request->get('search'));

                $query->where(function ($q) use ($search, $searchableColumns) {
                    foreach ($searchableColumns as $column) {
                        $q->orWhere($column, 'like', '%' . $search . '%');
                    }

                    if (is_numeric($search)) {
                        $q->orWhere('id', $search)
                        ->orWhere('total_vehicle', $search)
                        ->orWhere('car', $search)
                        ->orWhere('motorbike', $search)
                        ->orWhere('bus', $search)
                        ->orWhere('truck', $search)
                        ->orWhere('van', $search)
                        ->orWhere('suv', $search)
                        ->orWhere('fire_engine', $search)
                        ->orWhere('ambulance', $search)
                        ->orWhere('bicycle', $search)
                        ->orWhere('other', $search)
                        ->orWhere('_local_lane', $search)
                        ->orWhere('_global_lane', $search);
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */
            $sortBy = $request->get('sort_by', 'event_time');
            $sortOrder = strtolower($request->get('sort_order', 'desc'));

            if (!in_array($sortBy, $sortableColumns, true)) {
                $sortBy = 'event_time';
            }

            if (!in_array($sortOrder, ['asc', 'desc'], true)) {
                $sortOrder = 'desc';
            }

            $query->orderBy($sortBy, $sortOrder);

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */
            $paginator = $query->paginate($perPage)->appends($request->query());

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Vehicle counts fetched successfully.',
                'count' => $paginator->total(),
                'previous' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'results' => VehicleCountResource::collection($paginator->items()),
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to fetch vehicle counts.',
                'error' => $e->getMessage(),
                'count' => 0,
                'previous' => null,
                'next' => null,
                'results' => [],
            ], 500);
        }
    }
   

    public function show($id)
    {
        try {
            $vehicleCount = VehicleCount::find($id);

            if (!$vehicleCount) {
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Vehicle count not found.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Vehicle count fetched successfully.',
                'data' => new VehicleCountResource($vehicleCount),
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to fetch vehicle count.',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $vehicleCount = VehicleCount::find($id);

            if (!$vehicleCount) {
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Vehicle count not found.',
                    'data' => null,
                ], 404);
            }

            $this->deleteImageIfExists($vehicleCount->full_snapshot_path);
            $vehicleCount->delete();

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Vehicle count deleted successfully.',
                'data' => null,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to delete vehicle count.',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function upsert(StoreVehicleCountRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $vehicleCount = VehicleCount::where('device_name', $validated['device_name'])
                ->where('mac_address', $validated['mac_address'])
                ->where('detection_region_name', $validated['detection_region_name'])
                ->first();

            $imagePath = $vehicleCount?->full_snapshot_path;

            if (!empty($validated['full_snapshot'])) {
                if ($imagePath) {
                    $this->deleteImageIfExists($imagePath);
                }
                $imagePath = $this->saveBase64Image($validated['full_snapshot']);
            }

            $vehicleCount = VehicleCount::updateOrCreate(
                [
                    'device_name' => $validated['device_name'],
                    'mac_address' => $validated['mac_address'],
                    'detection_region_name' => $validated['detection_region_name'],
                ],
                [
                    'event_type' => $validated['event_type'],
                    'sn' => $validated['sn'] ?? null,
                    'event_time' => $validated['time'],
                    'detection_region' => $validated['detection_region'] ?? null,

                    'total_vehicle' => $validated['total_vehicle'] ?? 0,
                    'count_type_car' => $validated['count_type_car'] ?? 0,
                    'count_type_motorcycle' => $validated['count_type_motorcycle'] ?? 0,
                    'count_type_non_motor' => $validated['count_type_non_motor'] ?? 0,
                    'count_type_small_vehicle' => $validated['count_type_small_vehicle'] ?? 0,
                    'count_type_medium_vehicle' => $validated['count_type_medium_vehicle'] ?? 0,
                    'count_type_large_vehicle' => $validated['count_type_large_vehicle'] ?? 0,

                    'car' => $validated['car'] ?? 0,
                    'motorbike' => $validated['motorbike'] ?? 0,
                    'bus' => $validated['bus'] ?? 0,
                    'truck' => $validated['truck'] ?? 0,
                    'van' => $validated['van'] ?? 0,
                    'suv' => $validated['suv'] ?? 0,
                    'fire_engine' => $validated['fire_engine'] ?? 0,
                    'ambulance' => $validated['ambulance'] ?? 0,
                    'bicycle' => $validated['bicycle'] ?? 0,
                    'other' => $validated['other'] ?? 0,

                    'full_snapshot_path' => $imagePath,

                    '_camera' => $validated['_camera'] ?? null,
                    '_camera_mac' => $validated['_camera_mac'] ?? null,
                    '_camera_identifier' => $validated['_camera_identifier'] ?? null,
                    '_local_lane' => $validated['_local_lane'] ?? null,
                    '_global_lane' => $validated['_global_lane'] ?? null,

                    'raw_payload' => $request->all(),
                ]
            );

            DB::commit();

            return new VehicleCountResource($vehicleCount);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to upsert vehicle count.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function saveOrUpdate(StoreVehicleCountRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $existing = VehicleCount::where('device_name', $validated['device_name'])
                ->where('mac_address', $validated['mac_address'])
                ->where('detection_region_name', $validated['detection_region_name'])
                ->first();

            $imagePath = $existing?->full_snapshot_path;

            if (!empty($validated['full_snapshot'])) {
                if ($existing && $imagePath) {
                    $this->deleteImageIfExists($imagePath);
                }

                $imagePath = $this->saveBase64Image($validated['full_snapshot']);
            }

            $vehicleCount = VehicleCount::updateOrCreate(
                [
                    'device_name' => $validated['device_name'],
                    'mac_address' => $validated['mac_address'],
                    'detection_region_name' => $validated['detection_region_name'],
                ],
                [
                    'event_type' => $validated['event_type'],
                    'sn' => $validated['sn'] ?? null,
                    'event_time' => $validated['time'],
                    'detection_region' => $validated['detection_region'] ?? null,

                    'total_vehicle' => $validated['total_vehicle'] ?? 0,
                    'count_type_car' => $validated['count_type_car'] ?? 0,
                    'count_type_motorcycle' => $validated['count_type_motorcycle'] ?? 0,
                    'count_type_non_motor' => $validated['count_type_non_motor'] ?? 0,
                    'count_type_small_vehicle' => $validated['count_type_small_vehicle'] ?? 0,
                    'count_type_medium_vehicle' => $validated['count_type_medium_vehicle'] ?? 0,
                    'count_type_large_vehicle' => $validated['count_type_large_vehicle'] ?? 0,

                    'car' => $validated['car'] ?? 0,
                    'motorbike' => $validated['motorbike'] ?? 0,
                    'bus' => $validated['bus'] ?? 0,
                    'truck' => $validated['truck'] ?? 0,
                    'van' => $validated['van'] ?? 0,
                    'suv' => $validated['suv'] ?? 0,
                    'fire_engine' => $validated['fire_engine'] ?? 0,
                    'ambulance' => $validated['ambulance'] ?? 0,
                    'bicycle' => $validated['bicycle'] ?? 0,
                    'other' => $validated['other'] ?? 0,

                    'full_snapshot_path' => $imagePath,

                    '_camera' => $validated['_camera'] ?? null,
                    '_camera_mac' => $validated['_camera_mac'] ?? null,
                    '_camera_identifier' => $validated['_camera_identifier'] ?? null,
                    '_local_lane' => $validated['_local_lane'] ?? null,
                    '_global_lane' => $validated['_global_lane'] ?? null,

                    'raw_payload' => $request->all(),
                ]
            );

            DB::commit();

            $statusCode = $existing ? 200 : 201;

            return response()->json([
                'status' => true,
                'code' => $statusCode,
                'message' => $existing
                    ? 'Vehicle count updated successfully.'
                    : 'Vehicle count created successfully.',
                'data' => new VehicleCountResource($vehicleCount),
            ], $statusCode);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to save vehicle count data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}