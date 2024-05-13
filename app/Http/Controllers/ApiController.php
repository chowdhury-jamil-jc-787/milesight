<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Random;

class ApiController extends Controller
{
    public function handleNotification(Request $request)
    {
        // Generate a random value
        $randomValue = mt_rand(1000, 9999);

        // Store the random value in the database (you should have a database table and model for this)
        // Replace 'your_table_name' with your actual table name
        Random::create(['random_column' => $randomValue]);

        // Return a response (optional)
        return response()->json(['message' => 'Random value stored successfully']);
    }

    public function handle(Request $request)
    {
        \Log::info('Received HTTP Notification:', $request->all());

        // Your custom logic

        return response('Notification handled successfully.');
    }
}
