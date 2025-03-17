<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function ApiHit(Request $request)
    {
        try {
            // Extract JSON data from the request
            $jsonData = $request->json()->all();

            // Send the JSON data to the external API
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache, private',
                'X-RateLimit-Limit' => '60',
                'X-RateLimit-Remaining' => '59',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Max-Age' => '3600',
                'Upgrade' => 'h2,h2c',
                'Connection' => 'Upgrade, Keep-Alive',
                'Vary' => 'Accept-Encoding',
                'Content-Encoding' => 'gzip',
                'Keep-Alive' => 'timeout=5, max=75'
            ])->post('https://milesight.trafficiot.com/api/store-event-data', $jsonData);

            // Check the response status and act accordingly
            if ($response->successful()) {
                return response()->json(['message' => 'JSON data received and sent successfully.'], 200);
            } else {
                return response()->json(['message' => 'Failed to send JSON data.', 'error' => $response->body()], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to decode JSON data.', 'error' => $e->getMessage()], 400);
        }
    }
}
