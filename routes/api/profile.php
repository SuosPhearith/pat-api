<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\MyProfileController;

Route::get('/',                 [MyProfileController::class, 'view']);
Route::post('/',                [MyProfileController::class, 'update']);
Route::post('/change-password', [MyProfileController::class, 'changePassword']);

