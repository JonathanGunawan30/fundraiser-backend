<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\Tag;
use App\Services\Interfaces\TagServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_returns_success_response()
    {
        $this->mock(TagServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('getAllTags')->once()->with(10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/tags?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tags retrieved successfully'
            ]);
    }

    public function test_show_returns_success_response()
    {
        $tag = new Tag(['name' => 'Test Tag', 'slug' => 'test-tag']);
        $tag->id = 1;

        $this->mock(TagServiceInterface::class, function (MockInterface $mock) use ($tag) {
            $mock->shouldReceive('getTagById')->once()->with(1)->andReturn($tag);
        });

        $response = $this->getJson('/api/tags/1');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'name' => 'Test Tag'
                ]
            ]);
    }

    public function test_store_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $tagData = ['name' => 'New Tag', 'slug' => 'new-tag'];
        $tag = new Tag(array_merge(['id' => 1], $tagData));
        $tag->id = 1;

        $this->mock(TagServiceInterface::class, function (MockInterface $mock) use ($tag, $tagData) {
            $mock->shouldReceive('createTag')->once()->with($tagData)->andReturn($tag);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/tags', $tagData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Tag created successfully',
                'data' => ['name' => 'New Tag']
            ]);
    }

    public function test_update_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $tagData = ['name' => 'Updated Tag'];
        $tag = new Tag(['name' => 'Updated Tag', 'slug' => 'old-slug']);
        $tag->id = 1;

        $this->mock(TagServiceInterface::class, function (MockInterface $mock) use ($tag, $tagData) {
            $mock->shouldReceive('updateTag')->once()->with(1, $tagData)->andReturn($tag);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson('/api/admin/tags/1', $tagData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tag updated successfully',
                'data' => ['name' => 'Updated Tag']
            ]);
    }

    public function test_destroy_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->mock(TagServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteTag')->once()->with(1)->andReturn(true);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson('/api/admin/tags/1');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tag deleted successfully'
            ]);
    }

    public function test_search_returns_success_response()
    {
        $this->mock(TagServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('searchTags')->once()->with('test', 10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/tags/search?keyword=test&per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tags search results retrieved successfully'
            ]);
    }
}
