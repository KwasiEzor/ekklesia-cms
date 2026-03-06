<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampusController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\GalleryController;
use App\Http\Controllers\Api\V1\GivingRecordController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\SermonController;
use App\Http\Middleware\InitializeTenancyByHeader;
use App\Http\Middleware\InitializeTenancyByUser;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Public auth endpoints — tenant identified via X-Tenant-ID header
    Route::prefix('auth')
        ->middleware([InitializeTenancyByHeader::class, 'throttle:auth'])
        ->group(function (): void {
            Route::post('register', [AuthController::class, 'register']);
            Route::post('login', [AuthController::class, 'login']);
        });

    // Authenticated auth endpoints — tenant initialized from user
    Route::prefix('auth')
        ->middleware(['auth:sanctum', InitializeTenancyByUser::class, 'throttle:api'])
        ->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::get('tokens', [AuthController::class, 'tokens']);
            Route::delete('tokens', [AuthController::class, 'revokeAllTokens']);
            Route::delete('tokens/{token}', [AuthController::class, 'revokeToken']);
        });

    // Content API — tenant initialized from authenticated user
    Route::middleware(['auth:sanctum', InitializeTenancyByUser::class, 'throttle:api'])->group(function (): void {
        Route::apiResource('campuses', CampusController::class);
        Route::apiResource('sermons', SermonController::class);
        Route::apiResource('events', EventController::class);
        Route::apiResource('announcements', AnnouncementController::class);
        Route::apiResource('members', MemberController::class);
        Route::apiResource('galleries', GalleryController::class);
        Route::apiResource('pages', PageController::class);
        Route::apiResource('giving-records', GivingRecordController::class)->except(['update', 'destroy']);

        // Payments — requires plan with payments feature
        Route::middleware(['plan:payments'])->group(function (): void {
            Route::post('payments/initiate', [PaymentController::class, 'initiate']);
            Route::get('payments', [PaymentController::class, 'index']);
            Route::get('payments/{uuid}', [PaymentController::class, 'show']);
        });
    });

    // Payment webhooks — no auth, no tenant header (providers don't send it).
    // Tenant resolved from the transaction itself in the controller.
    Route::post('payments/webhook/{provider}', [PaymentController::class, 'webhook'])
        ->name('api.payments.webhook');
});
