<?php

namespace App\Http\Controllers;

use App\Models\LidarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LidarEventController extends Controller
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

        $query = LidarEvent::query();

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

        $lidarEvents = $query->paginate($perPage);

        if ($onlyDeviceName) {
            $results = $lidarEvents->pluck('device_name')->all();
        } elseif ($onlyEventType) {
            $results = $lidarEvents->pluck('event_type')->all();
        } else {
            $results = $lidarEvents->items();
        }

        return response()->json([
            'count' => $lidarEvents->total(),
            'next' => $lidarEvents->nextPageUrl(),
            'previous' => $lidarEvents->previousPageUrl(),
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

        $exists = LidarEvent::where('time_stamp', $request->time_stamp)
            ->where('event_type', $request->event_type)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'An event with the same time stamp and event type already exists.'
            ], 409);
        }

        $lidarEvent = LidarEvent::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Lidar event created successfully',
            'data' => $lidarEvent
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LidarEvent $lidarEvent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LidarEvent $lidarEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LidarEvent $lidarEvent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LidarEvent $lidarEvent)
    {
        try {
            $lidarEvent->delete();

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
