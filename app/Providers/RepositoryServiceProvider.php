<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Interfaces\AuthRepositoryInterface::class,
            \App\Repositories\Implementations\AuthRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CampaignCategoryRepositoryInterface::class,
            \App\Repositories\Implementations\CampaignCategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\Implementations\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\AdminRepositoryInterface::class,
            \App\Repositories\Implementations\AdminRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\TagRepositoryInterface::class,
            \App\Repositories\Implementations\TagRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CampaignRepositoryInterface::class,
            \App\Repositories\Implementations\CampaignRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CampaignUpdateRepositoryInterface::class,
            \App\Repositories\Implementations\CampaignUpdateRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\CampaignImageRepositoryInterface::class,
            \App\Repositories\Implementations\CampaignImageRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\DonationRepositoryInterface::class,
            \App\Repositories\Implementations\DonationRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\DonationPaymentRepositoryInterface::class,
            \App\Repositories\Implementations\DonationPaymentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\WithdrawalRepositoryInterface::class,
            \App\Repositories\Implementations\WithdrawalRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\BannerRepositoryInterface::class,
            \App\Repositories\Implementations\BannerRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\BannerPlacementRepositoryInterface::class,
            \App\Repositories\Implementations\BannerPlacementRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\FaqRepositoryInterface::class,
            \App\Repositories\Implementations\FaqRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\NotificationRepositoryInterface::class,
            \App\Repositories\Implementations\NotificationRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\SiteSettingRepositoryInterface::class,
            \App\Repositories\Implementations\SiteSettingRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\OauthAccountRepositoryInterface::class,
            \App\Repositories\Implementations\OauthAccountRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\StatRepositoryInterface::class,
            \App\Repositories\Implementations\StatRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\AuditLogRepositoryInterface::class,
            \App\Repositories\Implementations\AuditLogRepository::class
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
