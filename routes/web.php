<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZoomController;
use App\Http\Middleware;
use App\Http\Controllers\ZoomTokenController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/zoom/token', [ZoomTokenController::class, 'fetchToken'])->middleware('cors');
Route::post('/zoom/meetings', [ZoomController::class, 'createMeeting'])->middleware('cors');
Route::get('/zoom/meetings', [ZoomController::class, 'listMeetings'])->middleware('cors');
