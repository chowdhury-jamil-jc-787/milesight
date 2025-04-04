<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RadarEventController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ValueController;

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

Route::post('/serverevents', [ServerController::class, 'store']);
Route::get('/serverevents', [ServerController::class, 'index']);


Route::post('/apiHit', [ApiController::class, 'ApiHit']);


Route::apiResource('values', ValueController::class);