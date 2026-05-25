<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Interfaces\AuthServiceInterface::class,
            \App\Services\Implementations\AuthService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\CampaignCategoryServiceInterface::class,
            \App\Services\Implementations\CampaignCategoryService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\UserServiceInterface::class,
            \App\Services\Implementations\UserService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\AdminServiceInterface::class,
            \App\Services\Implementations\AdminService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\TagServiceInterface::class,
            \App\Services\Implementations\TagService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\CampaignServiceInterface::class,
            \App\Services\Implementations\CampaignService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\CampaignUpdateServiceInterface::class,
            \App\Services\Implementations\CampaignUpdateService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\CampaignImageServiceInterface::class,
            \App\Services\Implementations\CampaignImageService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\DonationServiceInterface::class,
            \App\Services\Implementations\DonationService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\DonationPaymentServiceInterface::class,
            \App\Services\Implementations\DonationPaymentService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\WithdrawalServiceInterface::class,
            \App\Services\Implementations\WithdrawalService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\BannerServiceInterface::class,
            \App\Services\Implementations\BannerService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\BannerPlacementServiceInterface::class,
            \App\Services\Implementations\BannerPlacementService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\FaqServiceInterface::class,
            \App\Services\Implementations\FaqService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\NotificationServiceInterface::class,
            \App\Services\Implementations\NotificationService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\SiteSettingServiceInterface::class,
            \App\Services\Implementations\SiteSettingService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\OauthAccountServiceInterface::class,
            \App\Services\Implementations\OauthAccountService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\DashboardServiceInterface::class,
            \App\Services\Implementations\DashboardService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\AuditLogServiceInterface::class,
            \App\Services\Implementations\AuditLogService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
