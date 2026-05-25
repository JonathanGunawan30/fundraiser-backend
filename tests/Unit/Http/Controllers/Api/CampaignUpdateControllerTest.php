<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;
use App\Services\Interfaces\CampaignUpdateServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Mockery;
use Tests\TestCase;

class CampaignUpdateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_success_response()
    {
        $this->mock(CampaignUpdateServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('getAllUpdates')->once()->with(10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/campaign-updates?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Campaign updates retrieved successfully'
            ]);
    }

    public function test_show_returns_success_response()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        $update = new CampaignUpdate([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'title' => 'Test Update',
            'content' => 'Content'
        ]);
        $update->id = 1;

        $this->mock(CampaignUpdateServiceInterface::class, function (MockInterface $mock) use ($update) {
            $mock->shouldReceive('getUpdateById')->once()->with(1)->andReturn($update);
        });

        $response = $this->getJson('/api/campaign-updates/1');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'title' => 'Test Update'
                ]
            ]);
    }

    public function test_store_returns_success_response()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        
        $updateData = [
            'campaign_id' => $campaign->id,
            'title' => 'New Update',
            'content' => 'Content',
        ];
        
        $update = new CampaignUpdate(array_merge(['id' => 1], $updateData));
        $update->id = 1;

        $this->mock(CampaignUpdateServiceInterface::class, function (MockInterface $mock) use ($update) {
            $mock->shouldReceive('createUpdate')->once()->andReturn($update);
        });

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/campaign-updates', $updateData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Campaign update created successfully',
                'data' => ['title' => 'New Update']
            ]);
    }
}
