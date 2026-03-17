<?php

namespace App\Http\Controllers;

use App\Models\LidarEvent2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LidarEvent2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $eventTypeFilter = $request->get('event_type');
        $deviceNameSearch = $request->get('device_name');
        $onlyDeviceName = filter_var($request->get('only_device_name'), FILTER_VALIDATE_BOOLEAN);
        $onlyEventType = filter_var($request->get('only_event_type'), FILTER_VALIDATE_BOOLEAN);

        $query = LidarEvent2::query();

        if ($eventTypeFilter) {
            $query->where('event_type', $eventTypeFilter);
        }

        if ($deviceNameSearch) {
            $query->where('device_name', 'LIKE', '%' . $deviceNameSearch . '%');
        }

        if ($onlyDeviceName) {
            $query->select('device_name')->distinct();
        } elseif ($onlyEventType) {
            $query->select('event_type')->distinct();
            $query->orderBy('event_type', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $LidarEvent2s = $query->paginate($perPage);

        if ($onlyDeviceName) {
            $results = $LidarEvent2s->pluck('device_name')->all();
        } elseif ($onlyEventType) {
            $results = $LidarEvent2s->pluck('event_type')->all();
        } else {
            $results = $LidarEvent2s->items();
        }

        return response()->json([
            'count' => $LidarEvent2s->total(),
            'next' => $LidarEvent2s->nextPageUrl(),
            'previous' => $LidarEvent2s->previousPageUrl(),
            'results' => $results,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'required|string|max:255',
            'event_type' => 'required|string|max:255',
            'event_desc' => 'required|string',
            'time_stamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = LidarEvent2::where('time_stamp', $request->time_stamp)
            ->where('event_type', $request->event_type)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'An event with the same time stamp and event type already exists.'
            ], 409);
        }

        $LidarEvent2 = LidarEvent2::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Lidar event created successfully',
            'data' => $LidarEvent2
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LidarEvent2 $LidarEvent2)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LidarEvent2 $LidarEvent2)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LidarEvent2 $LidarEvent2)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LidarEvent2 $LidarEvent2)
    {
        try {
            $LidarEvent2->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Lidar event deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete the lidar event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
