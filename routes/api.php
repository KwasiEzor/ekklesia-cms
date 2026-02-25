<?php

use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\SermonController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('sermons', SermonController::class);
    Route::apiResource('events', EventController::class);
});
