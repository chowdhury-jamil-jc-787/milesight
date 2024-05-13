<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    // Function to store event data into the database
    public function storeEventData(Request $request)
    {
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
            'speed' => 'numeric|nullable',
            'data_list' => 'json|nullable',
            'resolution_width' => 'integer|nullable',
            'resolution_height' => 'integer|nullable',
            'coordinate_x1' => 'integer|nullable',
            'coordinate_y1' => 'integer|nullable',
            'coordinate_x2' => 'integer|nullable',
            'coordinate_y2' => 'integer|nullable',
            'license_plate_snapshot' => 'string|nullable',
            'vehicle_snapshot' => 'string|nullable',
            'full_snapshot' => 'string|nullable',
            'violation_snapshot' => 'string|nullable',
            'evidence_snapshot0' => 'string|nullable',
            'evidence_snapshot1' => 'string|nullable',
        ]);
    
        // Create a new Event instance
        $event = new Event();
    
        // Assign values from the request to the Event model properties
        $event->event_type = $request->event_type;
        $event->device_name = $request->device_name;
        $event->mac_address = $request->mac_address;
        $event->sn = $request->sn;
        $event->time = $request->time;
        $event->detection_region = $request->detection_region;
        $event->detection_region_name = $request->detection_region_name;
        $event->license_plate = $request->license_plate;
        $event->country_region = $request->country_region;
        $event->plate_type = $request->plate_type;
        $event->plate_color = $request->plate_color;
        $event->vehicle_type = $request->vehicle_type;
        $event->vehicle_color = $request->vehicle_color;
        $event->vehicle_brand = $request->vehicle_brand;
        $event->direction = $request->direction;
        $event->speed = $request->speed;
        $event->data_list = $request->data_list;
        $event->resolution_width = $request->resolution_width;
        $event->resolution_height = $request->resolution_height;
        $event->coordinate_x1 = $request->coordinate_x1;
        $event->coordinate_y1 = $request->coordinate_y1;
        $event->coordinate_x2 = $request->coordinate_x2;
        $event->coordinate_y2 = $request->coordinate_y2;
        $event->vehicle_tracking_box_x1 = $request->vehicle_tracking_box_x1;
        $event->vehicle_tracking_box_y1 = $request->vehicle_tracking_box_y1;
        $event->vehicle_tracking_box_x2 = $request->vehicle_tracking_box_x2;
        $event->vehicle_tracking_box_y2 = $request->vehicle_tracking_box_y2;
    
        // Store images
        $event->license_plate_snapshot = $this->storeImage($request->license_plate_snapshot, 'license_plate_snapshot');
        $event->vehicle_snapshot = $this->storeImage($request->vehicle_snapshot, 'vehicle_snapshot');
        $event->full_snapshot = $this->storeImage($request->full_snapshot, 'full_snapshot');
        $event->violation_snapshot = $this->storeImage($request->violation_snapshot, 'violation_snapshot');
        $event->evidence_snapshot0 = $this->storeImage($request->evidence_snapshot0, 'evidence_snapshot0');
        $event->evidence_snapshot1 = $this->storeImage($request->evidence_snapshot1, 'evidence_snapshot1');
    
        // Save the Event instance to the database
        $event->save();
    
        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Event data stored successfully'], 201);
    }
    
    // Helper function to store base64 encoded image as file
    private function storeImage($base64Image, $prefix)
    {
        $imageData = base64_decode($base64Image);
        $imageName = $prefix . '_' . time() . '_' . uniqid() . '.jpg';
        $imagePath = 'images/' . $imageName;
    
        // Store the image in the public folder
        if (Storage::disk('public')->put($imagePath, $imageData)) {
            info('Image stored successfully: ' . $imagePath);
        } else {
            error_log('Error storing image: ' . $imagePath);
        }
    
        return $imagePath;
    }
    
}
