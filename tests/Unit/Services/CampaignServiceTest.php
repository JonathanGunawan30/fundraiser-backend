<?php

namespace Tests\Unit\Services;

use App\Models\Campaign;
use App\Repositories\Interfaces\CampaignRepositoryInterface;
use App\Repositories\Interfaces\CampaignImageRepositoryInterface;
use App\Services\Implementations\CampaignService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CampaignServiceTest extends TestCase
{
    public function test_create_campaign_processes_uploads_and_tags()
    {
        Storage::fake('r2');
        $file = UploadedFile::fake()->image('cover.jpg');
        
        $data = [
            'title' => 'Test Campaign',
            'cover_image' => $file,
            'tags' => [1, 2],
            'images' => [UploadedFile::fake()->image('gallery.jpg')]
        ];

        $campaign = new Campaign(['title' => 'Test Campaign']);
        $campaign->id = 1;

        $this->mock(CampaignRepositoryInterface::class, function (MockInterface $mock) use ($campaign) {
            $mock->shouldReceive('create')->once()->andReturn($campaign);
            $mock->shouldReceive('syncTags')->once()->with($campaign, [1, 2]);
        });

        $this->mock(CampaignImageRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('createMany')->once();
        });

        $service = app(CampaignService::class);
        $result = $service->createCampaign($data);

        $this->assertSame($campaign, $result);
        
        $coverName = basename($result->cover_image_url);
        Storage::disk('r2')->assertExists('campaigns/covers/' . $coverName);
    }

    public function test_verify_campaign_updates_status_correctly()
    {
        $campaign = new Campaign(['id' => 1]);

        $this->mock(CampaignRepositoryInterface::class, function (MockInterface $mock) use ($campaign) {
            $mock->shouldReceive('update')->once()->with(1, Mockery::on(function($arg) {
                return $arg['verified_status'] === 'approved' && $arg['status'] === 'active';
            }))->andReturn($campaign);
        });

        $service = app(CampaignService::class);
        $service->verifyCampaign(1, 1, 'approved');
    }
}
