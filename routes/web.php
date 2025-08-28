<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// routes/web.php
Route::post('/resend-confirmation', [AuthController::class, 'resendConfirmationWeb'])
    ->name('verification.resend');
