<?php

namespace App\Http\Controllers;

use App\Models\ServerEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServerController extends Controller
{
    /**
 * Display a listing of the resource.
 */
public function index(Request $request)
{
    $perPage = $request->get('per_page', 10); // Default to 10 items per page if not specified
    $serverEvents = ServerEvent::orderBy('id', 'desc')->paginate($perPage);

    return response()->json([
        'count' => $serverEvents->total(),
        'next' => $serverEvents->nextPageUrl(),
        'previous' => $serverEvents->previousPageUrl(),
        'results' => $serverEvents->items(),
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

    $serverEvent = ServerEvent::create($request->all());

    return response()->json([
        'status' => 'success',
        'message' => 'Server event created successfully',
        'data' => $serverEvent
    ], 201);
}
}
