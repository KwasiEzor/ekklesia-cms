<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BulkMessageController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CampusController;
use App\Http\Controllers\Api\V1\DevotionalController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\FundController;
use App\Http\Controllers\Api\V1\GalleryController;
use App\Http\Controllers\Api\V1\GivingRecordController;
use App\Http\Controllers\Api\V1\HouseholdController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PrayerRequestController;
use App\Http\Controllers\Api\V1\ReadingPlanController;
use App\Http\Controllers\Api\V1\SermonController;
use App\Http\Controllers\Api\V1\ServiceTypeController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\TestimonyController;
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
        Route::get('members/upcoming/birthdays', [MemberController::class, 'birthdays']);
        Route::get('members/upcoming/anniversaries', [MemberController::class, 'anniversaries']);
        Route::apiResource('members', MemberController::class);
        Route::apiResource('galleries', GalleryController::class);
        Route::apiResource('pages', PageController::class);
        Route::apiResource('giving-records', GivingRecordController::class)->except(['update', 'destroy']);
        Route::apiResource('service-types', ServiceTypeController::class);
        Route::apiResource('attendances', AttendanceController::class);
        Route::apiResource('households', HouseholdController::class);
        Route::apiResource('prayer-requests', PrayerRequestController::class);
        Route::post('prayer-requests/{prayer_request}/commit', [PrayerRequestController::class, 'commit']);
        Route::apiResource('funds', FundController::class);
        Route::apiResource('campaigns', CampaignController::class);
        Route::apiResource('devotionals', DevotionalController::class);
        Route::apiResource('testimonies', TestimonyController::class);
        Route::post('testimonies/{testimony}/react', [TestimonyController::class, 'react']);
        Route::apiResource('reading-plans', ReadingPlanController::class);
        Route::post('reading-plans/{reading_plan}/subscribe', [ReadingPlanController::class, 'subscribe']);
        Route::post('reading-plans/{reading_plan}/days/{day}/complete', [ReadingPlanController::class, 'completeDay']);
        Route::get('reading-plans/{reading_plan}/progress', [ReadingPlanController::class, 'progress']);
        Route::apiResource('bulk-messages', BulkMessageController::class)->except(['update']);
        Route::post('bulk-messages/{bulk_message}/send', [BulkMessageController::class, 'send']);

        // Subscriptions
        Route::prefix('subscriptions')->group(function (): void {
            Route::get('status', [SubscriptionController::class, 'status']);
            Route::get('plans', [SubscriptionController::class, 'plans']);
            Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
            Route::post('portal', [SubscriptionController::class, 'portal']);
            Route::post('cancel', [SubscriptionController::class, 'cancel']);
            Route::post('resume', [SubscriptionController::class, 'resume']);
        });

        // Payments — requires plan with payments feature
        Route::middleware(['plan:payments'])->group(function (): void {
            Route::post('payments/initiate', [PaymentController::class, 'initiate']);
            Route::get('payments', [PaymentController::class, 'index']);
            Route::get('payments/{uuid}', [PaymentController::class, 'show']);
        });
    });

    // Payment webhooks — no auth, no tenant header (providers don't send it).
    // Tenant resolved from the transaction itself in the controller.
    Route::middleware(['throttle:webhooks'])->group(function (): void {
        Route::post('payments/webhook/{provider}', [PaymentController::class, 'webhook'])
            ->name('api.payments.webhook');

        Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
            ->name('api.stripe.webhook');
    });
});
