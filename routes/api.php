<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobCardController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('job-cards')->group(function () {
    Route::post('/', [JobCardController::class, 'store']);
    Route::get('/search-customers', [JobCardController::class, 'searchCustomers']);
    Route::get('/search-vehicles', [JobCardController::class, 'searchVehicles']);
});

Route::post('/webhooks/flutterwave', [\App\Http\Controllers\FlutterwaveWebhookController::class, 'handle']);
