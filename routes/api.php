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
use Illuminate\Support\Facades\Route;

// Prevent redirection hangs on unauthenticated API requests
Route::get('/login', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated',
        'errors' => null,
    ], 401);
})->name('login');

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
    });
});

Route::prefix('campaign-categories')->group(function () {
    Route::get('/', [CampaignCategoryController::class, 'index']);
    Route::get('/search', [CampaignCategoryController::class, 'search']);
    Route::get('/{id}', [CampaignCategoryController::class, 'show']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/search', [UserController::class, 'search']);
    Route::get('/{id}', [UserController::class, 'show']);
});

Route::prefix('admins')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::get('/search', [AdminController::class, 'search']);
    Route::get('/{id}', [AdminController::class, 'show']);
});

Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);
    Route::get('/search', [TagController::class, 'search']);
    Route::get('/{id}', [TagController::class, 'show']);
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

Route::prefix('campaign-images')->group(function () {
    Route::get('/', [CampaignImageController::class, 'index']);
    Route::get('/search', [CampaignImageController::class, 'search']);
    Route::get('/{id}', [CampaignImageController::class, 'show']);
});

Route::prefix('donations')->group(function () {
    Route::get('/', [DonationController::class, 'index']);
    Route::get('/search', [DonationController::class, 'search']);
    Route::get('/{id}', [DonationController::class, 'show']);
});

Route::prefix('donation-payments')->group(function () {
    Route::get('/', [DonationPaymentController::class, 'index']);
    Route::get('/search', [DonationPaymentController::class, 'search']);
    Route::get('/{id}', [DonationPaymentController::class, 'show']);
});

Route::prefix('withdrawals')->group(function () {
    Route::get('/', [WithdrawalController::class, 'index']);
    Route::get('/search', [WithdrawalController::class, 'search']);
    Route::get('/{id}', [WithdrawalController::class, 'show']);
});

Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
    Route::get('/search', [BannerController::class, 'search']);
    Route::get('/{id}', [BannerController::class, 'show']);
});

Route::prefix('banner-placements')->group(function () {
    Route::get('/', [BannerPlacementController::class, 'index']);
    Route::get('/search', [BannerPlacementController::class, 'search']);
    Route::get('/{id}', [BannerPlacementController::class, 'show']);
});

Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::get('/search', [FaqController::class, 'search']);
    Route::get('/{id}', [FaqController::class, 'show']);
});

Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/search', [NotificationController::class, 'search']);
    Route::get('/{id}', [NotificationController::class, 'show']);
});

Route::prefix('site-settings')->group(function () {
    Route::get('/', [SiteSettingController::class, 'index']);
    Route::get('/search', [SiteSettingController::class, 'search']);
    Route::get('/{id}', [SiteSettingController::class, 'show']);
});

Route::prefix('oauth-accounts')->group(function () {
    Route::get('/', [OauthAccountController::class, 'index']);
    Route::get('/search', [OauthAccountController::class, 'search']);
    Route::get('/{id}', [OauthAccountController::class, 'show']);
});
