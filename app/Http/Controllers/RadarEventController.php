<?php

namespace App\Http\Controllers;

use App\Models\RadarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RadarEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Default to 10 items per page if not specified
        $radarEvents = RadarEvent::orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'count' => $radarEvents->total(),
            'next' => $radarEvents->nextPageUrl(),
            'previous' => $radarEvents->previousPageUrl(),
            'results' => $radarEvents->items(),
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
        ], [
            'device_name.required' => 'The device name field is required.',
            'device_name.string' => 'The device name must be a string.',
            'device_name.max' => 'The device name may not be greater than 255 characters.',
            'event_type.required' => 'The event type field is required.',
            'event_type.string' => 'The event type must be a string.',
            'event_type.max' => 'The event type may not be greater than 255 characters.',
            'event_desc.required' => 'The event description field is required.',
            'event_desc.string' => 'The event description must be a string.',
            'time_stamp.required' => 'The time stamp field is required.',
            'time_stamp.date_format' => 'The time stamp does not match the format Y-m-d H:i:s.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $radarEvent = RadarEvent::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Radar event created successfully',
            'data' => $radarEvent
        ], 201);
    }

    

    /**
     * Display the specified resource.
     */
    public function show(RadarEvent $radarEvent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RadarEvent $radarEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RadarEvent $radarEvent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RadarEvent $radarEvent)
    {
        //
    }
}
