<?php

use App\Http\Controllers\JobCardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('job-cards')->middleware(['auth:sanctum', \App\Http\Middleware\EnforceSubscriptionAccess::class])->group(function () {
    Route::post('/', [JobCardController::class, 'store']);
    Route::get('/search-customers', [JobCardController::class, 'searchCustomers']);
    Route::get('/search-vehicles', [JobCardController::class, 'searchVehicles']);
});

Route::post('/webhooks/flutterwave', [\App\Http\Controllers\FlutterwaveWebhookController::class, 'handle']);
