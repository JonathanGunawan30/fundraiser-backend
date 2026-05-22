<?php

namespace Tests\Unit\Services;

use App\Models\SiteSetting;
use App\Repositories\Interfaces\SiteSettingRepositoryInterface;
use App\Services\Implementations\SiteSettingService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class SiteSettingServiceTest extends TestCase
{
    public function test_create_site_setting_calls_repository()
    {
        $data = ['key' => 'test_key', 'value' => 'test_value', 'type' => 'string'];

        $this->mock(SiteSettingRepositoryInterface::class, function (MockInterface $mock) use ($data) {
            $mock->shouldReceive('create')
                ->once()
                ->with($data)
                ->andReturn(new SiteSetting($data));
        });

        $service = app(SiteSettingService::class);
        $result = $service->createSiteSetting($data);

        $this->assertEquals('test_key', $result->key);
    }

    public function test_get_all_site_settings_returns_paginated_data()
    {
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mock(SiteSettingRepositoryInterface::class, function (MockInterface $mock) use ($mockPaginator, $perPage) {
            $mock->shouldReceive('getAllPaginated')->once()->with($perPage)->andReturn($mockPaginator);
        });

        $service = app(SiteSettingService::class);
        $result = $service->getAllSiteSettings($perPage);

        $this->assertSame($mockPaginator, $result);
    }
}
