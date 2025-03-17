<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\Random;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    // Function to store event data into the database
    public function storeEventData(Request $request)
    {
        // Define event type mapping rules
        $eventTypeMappings = [
            'Regular' => 'LPR'
        ];
    
        // Modify event_type without affecting other fields
        $request->merge([
            'event_type' => $eventTypeMappings[$request->event_type] ?? $request->event_type
        ]);
    
        // Validate the incoming request data
        $validatedData = $request->validate([
            'event_type' => 'required|string',
            'device_name' => 'required|string',
            'mac_address' => 'required|string',
            'sn' => 'required|string',
            'time' => 'required|date',
            'detection_region' => 'string|nullable',
            'detection_region_name' => 'string|nullable',
            'license_plate' => 'string|nullable',
            'country_region' => 'string|nullable',
            'plate_type' => 'string|nullable',
            'plate_color' => 'string|nullable',
            'vehicle_type' => 'string|nullable',
            'vehicle_color' => 'string|nullable',
            'vehicle_brand' => 'string|nullable',
            'direction' => 'string|nullable',
            'speed' => ['nullable', 'regex:/^\d+(\.\d+)?(km\/h)?$|^-|^No Speed$/'],
            'data_list' => 'array|nullable',
            'resolution_width' => 'string|nullable',
            'resolution_height' => 'string|nullable',
            'coordinate_x1' => 'string|nullable',
            'coordinate_y1' => 'string|nullable',
            'coordinate_x2' => 'string|nullable',
            'coordinate_y2' => 'string|nullable',
            'vehicle_tracking_box_x1' => 'string|nullable',
            'vehicle_tracking_box_y1' => 'string|nullable',
            'vehicle_tracking_box_x2' => 'string|nullable',
            'vehicle_tracking_box_y2' => 'string|nullable',
            'license_plate_snapshot' => 'string|nullable',
            'vehicle_snapshot' => 'string|nullable',
            'full_snapshot' => 'string|nullable',
            'violation_snapshot' => 'string|nullable',
            'evidence_snapshot0' => 'string|nullable',
            'evidence_snapshot1' => 'string|nullable',
        ]);
    
        // Properly extract numeric speed value (e.g., "30km/h" -> 30.0)
        if ($request->has('speed')) {
            preg_match('/\d+(\.\d+)?/', $request->speed, $matches);
            $validatedData['speed'] = isset($matches[0]) ? floatval($matches[0]) : 0;
        }
    
        // Convert data_list to JSON
        $validatedData['data_list'] = $request->has('data_list') ? json_encode($request->data_list) : null;
    
        // Store images and update validatedData
        $validatedData['license_plate_snapshot'] = $this->storeImage($request->license_plate_snapshot, 'license_plate_snapshot');
        $validatedData['vehicle_snapshot'] = $this->storeImage($request->vehicle_snapshot, 'vehicle_snapshot');
        $validatedData['full_snapshot'] = $this->storeImage($request->full_snapshot, 'full_snapshot');
        $validatedData['violation_snapshot'] = $this->storeImage($request->violation_snapshot, 'violation_snapshot');
        $validatedData['evidence_snapshot0'] = $this->storeImage($request->evidence_snapshot0, 'evidence_snapshot0');
        $validatedData['evidence_snapshot1'] = $this->storeImage($request->evidence_snapshot1, 'evidence_snapshot1');
    
        // Create and save the event
        Event::create($validatedData);
    
        // Return success response
        return response()->json(['message' => 'Event data stored successfully'], 201);
    }




    
    
    // Helper function to store base64 encoded image as file
    private function storeImage($base64Image, $prefix)
    {
        if (empty($base64Image)) {
            return 'images/null_placeholder.jpg'; // Save 'null_placeholder.jpg' if no image is provided
        }
    
        // Check if the base64 string has a prefix and remove it
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = $matches[1]; // Get file extension
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        } else {
            $extension = 'jpg'; // Default to jpg if no extension found
        }
    
        // Decode the base64 image
        $imageData = base64_decode($base64Image);
        if (!$imageData) {
            error_log('Failed to decode base64 image.');
            return 'images/default_placeholder.jpg'; // Save 'default_placeholder.jpg' if decoding fails
        }
    
        // Generate a unique filename
        $imageName = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
        $imagePath = 'images/' . $imageName;
    
        // Store the image in the public disk
        if (Storage::disk('public')->put($imagePath, $imageData)) {
            info('Image stored successfully: ' . $imagePath);
            return $imagePath;
        } else {
            error_log('Error storing image: ' . $imagePath);
            return 'images/default_placeholder.jpg'; // Save 'default_placeholder.jpg' if storage fails
        }
    }
    
    
    

    public function generateAndStoreRandomValue()
    {
        $randomValue = Str::random(5); // Adjust the length as needed
        Random::create(['random_value' => $randomValue]);
        return response("success");
    }


    public function index(Request $request)
    {
        // Get query parameters
        $startSpeed = $request->query('start_speed');
        $endSpeed = $request->query('end_speed');
        $startTime = $request->query('start_time');
        $endTime = $request->query('end_time');
        $direction = $request->query('direction');
        $deviceName = $request->query('device_name'); // Capture device_name parameter
    
        // Query the events table
        $events = Event::query();
    
        // Apply device_name filtering logic
        if ($deviceName !== null) {
            $events->where('device_name', $deviceName);
        }
    
        // Apply speed filtering logic
        if ($startSpeed !== null && $endSpeed !== null) {
            if ($startSpeed == $endSpeed) {
                $events->where('speed', (int)$startSpeed);
            } else {
                $events->whereBetween('speed', [(int)$startSpeed, (int)$endSpeed]);
            }
        } elseif ($startSpeed !== null) {
            $events->where('speed', '>=', (int)$startSpeed);
        }
    
        // Apply time filtering logic
        if ($startTime !== null && $endTime !== null) {
            if ($startTime == $endTime) {
                // Treat as a single day range
                $events->whereBetween('time', ["$startTime 00:00:00", "$endTime 23:59:59"]);
            } else {
                $events->whereBetween('time', [$startTime, $endTime]);
            }
        } elseif ($startTime !== null) {
            $events->where('time', '>=', "$startTime 00:00:00");
        }
    
        // Apply direction filtering logic
        if ($direction !== null && in_array($direction, ['Approach', 'Away'])) {
            $events->where('direction', $direction);
        }
    
        // Order results by time in descending order
        $events->orderBy('time', 'desc');
    
        // Paginate results
        $perPage = 15; // Default items per page
        $paginated = $events->paginate($perPage);
    
        // Transform the response to replace default placeholders with null
        $formattedResults = collect($paginated->items())->map(function ($event) {
            // Check if any snapshot has 'images/default_placeholder.jpg' and replace with null
            $placeholders = [
                'license_plate_snapshot',
                'vehicle_snapshot',
                'full_snapshot',
                'violation_snapshot',
                'evidence_snapshot0',
                'evidence_snapshot1'
            ];
    
            foreach ($placeholders as $field) {
                if ($event->$field === 'images/default_placeholder.jpg') {
                    $event->$field = null;
                }
            }
    
            return $event;
        });
    
        // Format the response with metadata
        $response = [
            'count' => $paginated->total(),
            'next' => $paginated->nextPageUrl(),
            'previous' => $paginated->previousPageUrl(),
            'results' => $formattedResults,
        ];
    
        // Return the results as a JSON response
        return response()->json($response);
    }
    

    public function listDeviceNames(Request $request)
    {
        // Query the events table for distinct device names
        $deviceNames = Event::distinct('device_name')->pluck('device_name');

        // Return the device names as a JSON response
        return response()->json(['device_names' => $deviceNames]);
    }




    
}
