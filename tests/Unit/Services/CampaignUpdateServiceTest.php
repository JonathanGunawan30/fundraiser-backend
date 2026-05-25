<?php

namespace Tests\Unit\Services;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Repositories\Interfaces\CampaignUpdateRepositoryInterface;
use App\Services\Implementations\CampaignUpdateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CampaignUpdateServiceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_create_update_processes_upload()
    {
        Storage::fake('r2');
        $file = UploadedFile::fake()->image('update.jpg');
        
        $user = \App\Models\User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $data = [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'title' => 'Test Update',
            'content' => 'Content',
            'image' => $file
        ];

        $capturedData = [];
        $this->mock(CampaignUpdateRepositoryInterface::class, function (MockInterface $mock) use (&$capturedData) {
            $mock->shouldReceive('create')
                ->once()
                ->with(Mockery::on(function($arg) use (&$capturedData) {
                    $capturedData = $arg;
                    return $arg['title'] === 'Test Update' && isset($arg['image_url']);
                }))
                ->andReturn(new CampaignUpdate());
        });

        $service = app(CampaignUpdateService::class);
        $service->createUpdate($data);
        
        $filename = basename($capturedData['image_url']);
        Storage::disk('r2')->assertExists('campaigns/updates/' . $filename);
    }

    public function test_create_update_throws_exception_on_unauthorized()
    {
        $user = \App\Models\User::factory()->create();
        $otherUser = \App\Models\User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $otherUser->id]);

        $data = [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'title' => 'Test Update',
            'content' => 'Content'
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);

        $service = app(CampaignUpdateService::class);
        $service->createUpdate($data);
    }
}
