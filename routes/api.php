<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CampaignUpdateController;
use App\Http\Controllers\Api\CampaignImageController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\DonationPaymentController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BannerPlacementController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\OauthAccountController;
use App\Http\Controllers\Api\CampaignCategoryController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'data' => [
            'name' => config('app.name'),
            'version' => '1.0.0',
            'php_version' => PHP_VERSION,
        ],
        'message' => 'FundRaiser API is online'
    ]);
});

// Prevent redirection hangs on unauthenticated API requests
Route::get('/login', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated',
        'errors' => null,
    ], 401);
})->name('login');

// Webhooks (Public)
Route::post('webhooks/midtrans', [WebhookController::class, 'handleMidtrans'])->name('webhooks.midtrans');

Route::prefix('admin')->group(function () {
    Route::post('otp', [AuthController::class, 'requestOtp']);
    Route::post('login', [AuthController::class, 'adminLogin']);
});

// Admin Routes
Route::prefix('admin')->middleware('auth:admin-api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('profile', [AdminController::class, 'profile']);
    Route::patch('profile', [AdminController::class, 'updateProfile']);
    Route::patch('password', [AdminController::class, 'updatePassword']);

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'adminIndex']);

    // Audit Logs
    Route::prefix('audit-logs')->group(function () {
        Route::get('/', [AuditLogController::class, 'index']);
        Route::get('/{id}', [AuditLogController::class, 'show']);
    });

    // FAQs
    Route::prefix('faqs')->group(function () {
        Route::post('/', [FaqController::class, 'store']);
        Route::put('/{id}', [FaqController::class, 'update']);
        Route::delete('/{id}', [FaqController::class, 'destroy']);
    });

    // Tags
    Route::prefix('tags')->group(function () {
        Route::post('/', [TagController::class, 'store']);
        Route::put('/{tag}', [TagController::class, 'update']);
        Route::delete('/{tag}', [TagController::class, 'destroy']);
    });

    // Banners
    Route::prefix('banners')->group(function () {
        Route::post('/', [BannerController::class, 'store']);
        Route::put('/{banner}', [BannerController::class, 'update']);
        Route::delete('/{banner}', [BannerController::class, 'destroy']);
    });

    // Campaign Categories
    Route::prefix('campaign-categories')->group(function () {
        Route::post('/', [CampaignCategoryController::class, 'store']);
        Route::put('/{campaign_category}', [CampaignCategoryController::class, 'update']);
        Route::delete('/{campaign_category}', [CampaignCategoryController::class, 'destroy']);
    });

    // Site Settings
    Route::prefix('site-settings')->group(function () {
        Route::post('/', [SiteSettingController::class, 'store']);
        Route::put('/{site_setting}', [SiteSettingController::class, 'update']);
        Route::delete('/{site_setting}', [SiteSettingController::class, 'destroy']);
    });

    // Campaigns
    Route::get('campaigns', [CampaignController::class, 'index']);

    // Campaign Verifications
    Route::post('campaigns/{id}/verify', [CampaignController::class, 'verify']);

    // Withdrawal Processing
    Route::post('withdrawals/{id}/process', [WithdrawalController::class, 'process']);

    // Admin Notifications (ponytail: implement endpoints for admin)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\AdminNotificationController::class, 'index']);
        Route::get('/unread', [\App\Http\Controllers\Api\AdminNotificationController::class, 'unread']);
        Route::patch('/{id}/read', [\App\Http\Controllers\Api\AdminNotificationController::class, 'markAsRead']);
        Route::post('/read-all', [\App\Http\Controllers\Api\AdminNotificationController::class, 'markAllAsRead']);
    });
});

// User Auth Routes
Route::prefix('auth')->group(function () {
    Route::middleware('auth:api')->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'userIndex']);

        // Profile
        Route::get('profile', [UserController::class, 'profile']);
        Route::patch('profile', [UserController::class, 'update']);

        // Campaigns
        Route::get('my-campaigns', [CampaignController::class, 'myCampaigns']);
        Route::prefix('campaigns')->group(function () {
            Route::post('/', [CampaignController::class, 'store']);
            Route::put('/{campaign}', [CampaignController::class, 'update']);
            Route::delete('/{campaign}', [CampaignController::class, 'destroy']);
        });

        // Campaign Updates
        Route::prefix('campaign-updates')->group(function () {
            Route::post('/', [CampaignUpdateController::class, 'store']);
            Route::put('/{campaign_update}', [CampaignUpdateController::class, 'update']);
            Route::delete('/{campaign_update}', [CampaignUpdateController::class, 'destroy']);
        });

        // Donations
        Route::prefix('donations')->group(function () {
            Route::get('/', [DonationController::class, 'index']);
            Route::post('/', [DonationController::class, 'store']);
            Route::get('/{number}', [DonationController::class, 'show']);
        });

        // Withdrawals
        Route::prefix('withdrawals')->group(function () {
            Route::post('/', [WithdrawalController::class, 'store']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            // Re-bind standard Laravel notifications relationship is automatic with Notifiable trait
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread', [NotificationController::class, 'unread']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });
    });
});

// Public Routes
Route::prefix('campaign-categories')->group(function () {
    Route::get('/', [CampaignCategoryController::class, 'index']);
    Route::get('/search', [CampaignCategoryController::class, 'search']);
    Route::get('/{id}', [CampaignCategoryController::class, 'show']);
});

Route::prefix('campaigns')->group(function () {
    Route::get('/', [CampaignController::class, 'index']);
    Route::get('/search', [CampaignController::class, 'search']);
    Route::get('/{slug}', [CampaignController::class, 'show']);
    Route::get('/{id}/donations', [CampaignController::class, 'donations']);
});

Route::prefix('campaign-updates')->group(function () {
    Route::get('/', [CampaignUpdateController::class, 'index']);
    Route::get('/search', [CampaignUpdateController::class, 'search']);
    Route::get('/{id}', [CampaignUpdateController::class, 'show']);
});

Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);
    Route::get('/search', [TagController::class, 'search']);
    Route::get('/{id}', [TagController::class, 'show']);
});

Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
    Route::get('/search', [BannerController::class, 'search']);
    Route::get('/{id}', [BannerController::class, 'show']);
});

Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::get('/search', [FaqController::class, 'search']);
    Route::get('/{id}', [FaqController::class, 'show']);
});

Route::prefix('site-settings')->group(function () {
    Route::get('/', [SiteSettingController::class, 'index']);
    Route::get('/search', [SiteSettingController::class, 'search']);
    Route::get('/{id}', [SiteSettingController::class, 'show']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/search', [UserController::class, 'search']);
    Route::get('/{id}', [UserController::class, 'show']);
});

Route::prefix('withdrawals')->group(function () {
    Route::get('/', [WithdrawalController::class, 'index']);
    Route::get('/search', [WithdrawalController::class, 'search']);
    Route::get('/{id}', [WithdrawalController::class, 'show']);
});
