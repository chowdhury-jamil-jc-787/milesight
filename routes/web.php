<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\TrafficEventController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/api/notification', [ApiController::class, 'handleNotification']);
Route::get('/handle-http-notification', [ApiController::class, 'handle']);


Route::post('/insert-data', [TrafficEventController::class, 'insertData']);
