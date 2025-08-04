<?php

use Illuminate\Support\Facades\Route;

//
Route::post('/contact', [App\Http\Controllers\WebContactController::class, 'handleContactForm']);

