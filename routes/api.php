<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RadarEventController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ValueController;
use App\Http\Controllers\GraphicController;
use App\Http\Controllers\LogFileUploadController;
use App\Http\Controllers\LidarEventController;
use App\Http\Controllers\Api\VehicleRecordController;
use App\Http\Controllers\Api\VehicleCountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/store-event-data', [EventController::class, 'storeEventData']);
Route::get('/store-event-data', [EventController::class, 'index']);
Route::get('/device-names', [App\Http\Controllers\EventController::class, 'listDeviceNames']);

Route::get('/randomNumber', [EventController::class, 'generateAndStoreRandomValue']);

Route::post('/events', [RadarEventController::class, 'store']);
Route::get('/events', [RadarEventController::class, 'index']);
Route::delete('/events/{radarEvent}', [RadarEventController::class, 'destroy']);

Route::post('/lidar-events', [LidarEventController::class, 'store']);
Route::get('/lidar-events', [LidarEventController::class, 'index']);
Route::delete('/lidar-events/{lidarEvent}', [LidarEventController::class, 'destroy']);

Route::post('/serverevents', [ServerController::class, 'store']);
Route::get('/serverevents', [ServerController::class, 'index']);


Route::post('/apiHit', [ApiController::class, 'ApiHit']);


Route::apiResource('values', ValueController::class);

Route::get('graphics/chart', [GraphicController::class, 'chart']);
Route::resource('graphics', GraphicController::class);

Route::apiResource('logfiles', LogFileUploadController::class);


Route::apiResource('vehicle-records', VehicleRecordController::class);


Route::prefix('vehicle-counts')->group(function () {
    Route::post('/save', [VehicleCountController::class, 'saveOrUpdate']);
    Route::get('/', [VehicleCountController::class, 'index']);
    Route::get('/{vehicleCount}', [VehicleCountController::class, 'show']);
    Route::delete('/{vehicleCount}', [VehicleCountController::class, 'destroy']);
});