<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login',                   [AuthController::class, 'login']);
Route::post('/register',                [AuthController::class, 'register']);
