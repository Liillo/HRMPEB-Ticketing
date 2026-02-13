<?php

use App\Http\Controllers\MpesaCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('/mpesa/callback', [MpesaCallbackController::class, 'callback']);
Route::post('/mpesa/timeout', [MpesaCallbackController::class, 'timeout']);