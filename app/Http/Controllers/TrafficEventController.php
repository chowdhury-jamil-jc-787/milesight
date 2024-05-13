<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TrafficEvent;

class TrafficEventController extends Controller
{
    public function insertData(Request $request)
    {
        $jsonData = $request->json()->all();

        // Create a new TrafficEvent model instance and fill it with data
        $trafficEvent = new TrafficEvent();
        $trafficEvent->event = $jsonData['event'];
        $trafficEvent->device = $jsonData['device'];
        $trafficEvent->time = $jsonData['time'] ?? null;
        $trafficEvent->region = $jsonData['region'] ?? null;
        $trafficEvent->region_name = $jsonData['region name'] ?? null;
        $trafficEvent->license_plate = $jsonData['license plate'] ?? null;
        $trafficEvent->plate_type = $jsonData['plate type'] ?? null;
        $trafficEvent->plate_color = $jsonData['plate color'] ?? null;
        $trafficEvent->vehicle_type = $jsonData['vehicle type'] ?? null;
        $trafficEvent->vehicle_color = $jsonData['vehicle color'] ?? null;
        $trafficEvent->vehicle_brand = $jsonData['vehicle brand'] ?? null;
        $trafficEvent->object_tracking_box_x1 = $jsonData['object tracking box_x1'] ?? null;
        $trafficEvent->object_tracking_box_y1 = $jsonData['object tracking box_y1'] ?? null;
        $trafficEvent->object_tracking_box_x2 = $jsonData['object tracking box_x2'] ?? null;
        $trafficEvent->object_tracking_box_y2 = $jsonData['object tracking box_y2'] ?? null;
        $trafficEvent->violation_jpg = $this->storeImage($jsonData['violation jpg'], 'violation');
        $trafficEvent->target_jpg = $this->storeImage($jsonData['target jpg'], 'target');

        // Save the model to the database
        $trafficEvent->save();

        return response()->json(['message' => 'Data inserted successfully']);
    }

    private function storeImage($base64Image, $prefix)
    {
        $imageData = base64_decode($base64Image);
        $imageName = $prefix . '_' . time() . '_' . uniqid() . '.jpg';
        $imagePath = 'images/' . $imageName;

        // Store the image in the public folder
        Storage::disk('public')->put($imagePath, $imageData);

        return $imagePath;
    }

    public function getTrafficEvents()
    {
        $trafficEvents = TrafficEvent::orderBy('id', 'desc')->get();

        // Append base URL to image paths
        $baseURL = 'https://milesight.trafficiot.com/milesight/storage/app/public/';
        $trafficEvents = $trafficEvents->map(function ($event) use ($baseURL) {
            $event->violation_jpg = $baseURL . $event->violation_jpg;
            $event->target_jpg = $baseURL . $event->target_jpg;
            return $event;
        });

        return response()->json($trafficEvents);
    }
}

