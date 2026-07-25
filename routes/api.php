<?php

use App\Http\Controllers\Api\SupplyGuardApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/countries', [SupplyGuardApiController::class, 'countries']);
    Route::get('/risk', [SupplyGuardApiController::class, 'risk']);
    Route::get('/ports', [SupplyGuardApiController::class, 'ports']);
    Route::get('/news', [SupplyGuardApiController::class, 'news']);
    Route::get('/currency', [SupplyGuardApiController::class, 'currency']);
});
