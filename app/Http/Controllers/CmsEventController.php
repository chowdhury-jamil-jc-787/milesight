<?php

namespace App\Http\Controllers;

use App\Models\CmsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CmsEventController extends Controller
{
    /**
     * Display a listing of CMS events.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $eventTypeFilter = $request->get('event_type');
        $deviceNameSearch = $request->get('device_name');

        $onlyDeviceName = filter_var(
            $request->get('only_device_name'),
            FILTER_VALIDATE_BOOLEAN
        );

        $onlyEventType = filter_var(
            $request->get('only_event_type'),
            FILTER_VALIDATE_BOOLEAN
        );

        $query = CmsEvent::query();

        if ($eventTypeFilter) {
            $query->where('event_type', $eventTypeFilter);
        }

        if ($deviceNameSearch) {
            $query->where('device_name', 'LIKE', '%' . $deviceNameSearch . '%');
        }

        if ($onlyDeviceName) {
            $query->select('device_name')->distinct();
            $query->orderBy('device_name', 'asc');
        } elseif ($onlyEventType) {
            $query->select('event_type')->distinct();
            $query->orderBy('event_type', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $cmsEvents = $query->paginate($perPage);

        if ($onlyDeviceName) {
            $results = collect($cmsEvents->items())->pluck('device_name')->all();
        } elseif ($onlyEventType) {
            $results = collect($cmsEvents->items())->pluck('event_type')->all();
        } else {
            $results = $cmsEvents->items();
        }

        return response()->json([
            'count' => $cmsEvents->total(),
            'next' => $cmsEvents->nextPageUrl(),
            'previous' => $cmsEvents->previousPageUrl(),
            'results' => $results,
        ]);
    }

    /**
     * Store a newly created CMS event.
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
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = CmsEvent::where('time_stamp', $request->time_stamp)
            ->where('event_type', $request->event_type)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'An event with the same time stamp and event type already exists.',
            ], 409);
        }

        $cmsEvent = CmsEvent::create($request->only([
            'device_name',
            'event_type',
            'event_desc',
            'time_stamp',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'CMS event created successfully',
            'data' => $cmsEvent,
        ], 201);
    }

    /**
     * Display the specified CMS event.
     */
    public function show(CmsEvent $cmsEvent)
    {
        return response()->json([
            'status' => 'success',
            'data' => $cmsEvent,
        ], 200);
    }

    /**
     * Update the specified CMS event.
     */
    public function update(Request $request, CmsEvent $cmsEvent)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'sometimes|required|string|max:255',
            'event_type' => 'sometimes|required|string|max:255',
            'event_desc' => 'sometimes|required|string',
            'time_stamp' => 'sometimes|required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $timeStamp = $request->get('time_stamp', $cmsEvent->time_stamp);
        $eventType = $request->get('event_type', $cmsEvent->event_type);

        $exists = CmsEvent::where('time_stamp', $timeStamp)
            ->where('event_type', $eventType)
            ->where('id', '!=', $cmsEvent->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'An event with the same time stamp and event type already exists.',
            ], 409);
        }

        $cmsEvent->update($request->only([
            'device_name',
            'event_type',
            'event_desc',
            'time_stamp',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'CMS event updated successfully',
            'data' => $cmsEvent,
        ], 200);
    }

    /**
     * Remove the specified CMS event.
     */
    public function destroy(CmsEvent $cmsEvent)
    {
        try {
            $cmsEvent->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'CMS event deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete the CMS event.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}