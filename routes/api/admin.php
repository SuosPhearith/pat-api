<?php

use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\TripController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ContryController;
// ============================================================================>> Custom Library
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BookingController;

// ===========================================================================>> Dashboard
Route::get('/dashboard', [DashboardController::class, 'getDashboardInfo']);
Route::get('/generate', [PrintController::class, 'printAllTripReportsWithBookingsOnly']);
// ===========================================================================>> POS
Route::prefix('bookings')->group(function () {
    Route::post('/', [BookingController::class, 'makeBooking']);
    Route::get('/getData', [BookingController::class, 'listBookings']);
    Route::get('/getByUser/{id}', [BookingController::class, 'listBookingsById']);
    // Route::put('/{id}', [BookingController::class, 'update']);
    // Route::delete('/{id}', [BookingController::class, 'destroy']);
});

// ===========================================================================>> Sale
// Route::group(['prefix' => 'sales'], function () {

//     Route::get('/',                         [SaleController::class, 'getData']);
//     Route::delete('/{id}',                  [SaleController::class, 'delete']);
//     Route::get('/print/{receipt_number}',   [PrintController::class, 'printInvoiceOrder']);

// });

// ===========================================================================>> Product
Route::group(['prefix' => 'countries'], function () {

    // // ===>> Product Type
    // Route::get('/types',        [ProductTypeController::class, 'getData']); // Read Multi Records
    // Route::post('/types',       [ProductTypeController::class, 'create']); // Create New Record
    // Route::post('/types/{id}',   [ProductTypeController::class, 'update']); // Update
    // Route::delete('/types/{id}', [ProductTypeController::class, 'delete']); // Delete a Record

    // ===>> Countries
    Route::get('/setup',        [ContryController::class, 'setUpData']); // Read Multi Records
    Route::get('/',        [ContryController::class, 'listing']); // Read Multi Records
    Route::get('/{id}',    [ContryController::class, 'view']); // View a Record
    Route::post('/',       [ContryController::class, 'create']); // Create New Record
    Route::post('/{id}',   [ContryController::class, 'update']); // Update
    Route::delete('/{id}', [ContryController::class, 'delete']); // Delete a Record
    // Route::get('/transactions/{id}', [ContryController::class, 'getProduct']);

});

Route::group(['prefix' => 'cities'], function () {

    // // ===>> Product Type
    // Route::get('/types',        [ProductTypeController::class, 'getData']); // Read Multi Records
    // Route::post('/types',       [ProductTypeController::class, 'create']); // Create New Record
    // Route::post('/types/{id}',   [ProductTypeController::class, 'update']); // Update
    // Route::delete('/types/{id}', [ProductTypeController::class, 'delete']); // Delete a Record

    // ===>> Product
    Route::get('/setup',        [CityController::class, 'setUpData']); // Read Multi Records
    Route::get('/',        [CityController::class, 'listing']); // Read Multi Records
    Route::get('/{id}',    [CityController::class, 'view']); // View a Record
    Route::post('/',       [CityController::class, 'create']); // Create New Record
    Route::post('/{id}',   [CityController::class, 'update']); // Update
    Route::delete('/{id}', [CityController::class, 'delete']); // Delete a Record
    // Route::get('/transactions/{id}', [ContryController::class, 'getProduct']);

});

Route::group(['prefix' => 'trips'], function () {

    // // ===>> Product Type
    // Route::get('/types',        [ProductTypeController::class, 'getData']); // Read Multi Records
    // Route::post('/types',       [ProductTypeController::class, 'create']); // Create New Record
    // Route::post('/types/{id}',   [ProductTypeController::class, 'update']); // Update
    // Route::delete('/types/{id}', [ProductTypeController::class, 'delete']); // Delete a Record

    // ===>> Product
    Route::get('/setup',        [TripController::class, 'setUpData']); // Read Multi Records
    Route::get('/',        [TripController::class, 'listing']); // Read Multi Records
    Route::get('/{id}',    [TripController::class, 'view']); // View a Record
    Route::post('/',       [TripController::class, 'create']); // Create New Record
    Route::post('/{id}',   [TripController::class, 'update']); // Update
    Route::delete('/{id}', [TripController::class, 'delete']); // Delete a Record
    // Route::get('/transactions/{id}', [ContryController::class, 'getProduct']);

});
// ===========================================================================>> User
Route::group(['prefix' => 'users'], function () {

    Route::get('/types',                    [UserController::class, 'getUserType']);
    Route::get('/', 						[UserController::class, 'getData']); // Read Many Records
    Route::get('/{id}', 					[UserController::class, 'view']); // View a Record
    Route::post('/', 						[UserController::class, 'create']); // Create New Record
    Route::post('/{id}', 					[UserController::class, 'update']); // Update Existing Record
    Route::delete('/{id}', 				    [UserController::class, 'delete']); // Delete a record

    Route::post('/block/{id}', 			    [UserController::class, 'block']); // Block a user. Make sure that he/she cannot login
    Route::post('/{id}/change-password',    [UserController::class, 'changePassword']); // Change the Password

});

