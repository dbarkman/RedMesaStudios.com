<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/contact', ContactController::class)
    ->middleware(['auth:sanctum', 'client.active', 'ip.banned', 'throttle:api']);
