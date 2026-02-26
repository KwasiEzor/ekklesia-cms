<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\GalleryController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\SermonController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('sermons', SermonController::class);
    Route::apiResource('events', EventController::class);
    Route::apiResource('announcements', AnnouncementController::class);
    Route::apiResource('members', MemberController::class);
    Route::apiResource('galleries', GalleryController::class);
    Route::apiResource('pages', PageController::class);
});
