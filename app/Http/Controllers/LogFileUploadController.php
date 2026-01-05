<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogFileUpload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class LogFileUploadController extends Controller
{
public function __construct()
{
    $this->middleware(function ($request, $next) {

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token missing'], 401);
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(10)
            ->get('https://backend.trafficiot.com/api/auth/profile');

        // TEMPORARY DEBUG
        return response()->json([
            'status' => $response->status(),
            'body'   => $response->body(),
            'json'   => $response->json(),
        ], $response->status());
    });
}


    // List all uploaded logs
    public function index()
    {
        // 10 items per page (you can change this)
        $logs = LogFileUpload::paginate(10);

        return response()->json([
            'current_page' => $logs->currentPage(),
            'per_page'     => $logs->perPage(),
            'total'        => $logs->total(),
            'last_page'    => $logs->lastPage(),

            // Navigation URLs
            'next_page_url'     => $logs->nextPageUrl(),
            'prev_page_url'     => $logs->previousPageUrl(),

            // Data list
            'results' => $logs->items()
        ]);
    }


    // Store a new log file record
    public function store(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string',
            'file'        => 'required|file',
            // remove date requirement
        ]);

        // Store file
        $filePath = $request->file('file')->store('log_files', 'public');

        $log = LogFileUpload::create([
            'device_name' => $request->device_name,
            'file'        => $filePath,
            'date'        => Carbon::today()->toDateString(), // auto today date
        ]);

        return response()->json($log, 201);
    }

    // Retrieve a single record
    public function show(LogFileUpload $logFileUpload)
    {
        return response()->json($logFileUpload);
    }

    // Delete a log
    public function destroy(LogFileUpload $logFileUpload)
    {
        $logFileUpload->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
