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
use Illuminate\Support\Facades\Route;

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

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'adminLogin']);

    Route::middleware('auth:admin-api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

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

        // Campaign Verifications
        Route::post('campaigns/{id}/verify', [CampaignController::class, 'verify']);
    });
});

Route::middleware('auth:api')->prefix('auth')->group(function () {
    // Campaigns (User Actions)
    Route::prefix('campaigns')->group(function () {
        Route::post('/', [CampaignController::class, 'store']);
        Route::put('/{campaign}', [CampaignController::class, 'update']);
        Route::delete('/{campaign}', [CampaignController::class, 'destroy']);
    });

    // Campaign Updates (User Actions)
    Route::prefix('campaign-updates')->group(function () {
        Route::post('/', [CampaignUpdateController::class, 'store']);
        Route::put('/{campaign_update}', [CampaignUpdateController::class, 'update']);
        Route::delete('/{campaign_update}', [CampaignUpdateController::class, 'destroy']);
    });

    // Donations (User Actions)
    Route::prefix('donations')->group(function () {
        Route::post('/', [DonationController::class, 'store']);
        Route::get('/{number}', [DonationController::class, 'show']);
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
    Route::get('/{id}', [CampaignController::class, 'show']);
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
