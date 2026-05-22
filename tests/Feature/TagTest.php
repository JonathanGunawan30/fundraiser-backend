<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tags()
    {
        Tag::factory()->count(5)->create();

        $response = $this->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug']
                ],
                'meta' => ['current_page', 'per_page', 'total'],
                'message' => []
            ]);
    }

    public function test_can_show_tag()
    {
        $tag = Tag::factory()->create([
            'name' => 'Education',
            'slug' => 'education'
        ]);

        $response = $this->getJson("/api/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $tag->id,
                    'name' => 'Education',
                    'slug' => 'education',
                ],
                'message' => 'Tag retrieved successfully'
            ]);
    }

    public function test_admin_can_create_tag()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'name' => 'New Tag',
            'slug' => 'new-tag'
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/auth/tags', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'New Tag',
                    'slug' => 'new-tag',
                ],
                'message' => 'Tag created successfully'
            ]);

        $this->assertDatabaseHas('tags', ['name' => 'New Tag']);
    }

    public function test_admin_can_update_tag()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $tag = Tag::factory()->create([
            'name' => 'Old Tag',
            'slug' => 'old-tag'
        ]);

        $data = [
            'name' => 'Updated Tag',
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson("/api/auth/tags/{$tag->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Tag',
                ],
                'message' => 'Tag updated successfully'
            ]);

        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'Updated Tag']);
    }

    public function test_admin_can_delete_tag()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $tag = Tag::factory()->create();

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson("/api/auth/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tag deleted successfully'
            ]);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_unauthorized_user_cannot_create_tag()
    {
        $data = [
            'name' => 'Unauth Tag',
            'slug' => 'unauth-tag'
        ];

        $response = $this->postJson('/api/auth/tags', $data);

        $response->assertStatus(401);
    }

    public function test_can_search_tags()
    {
        Tag::factory()->create(['name' => 'Environment']);
        Tag::factory()->create(['name' => 'Economy']);
        Tag::factory()->create(['name' => 'Health']);

        $response = $this->getJson('/api/tags/search?keyword=Eco');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Economy'])
            ->assertJsonMissing(['name' => 'Environment']) // because search is case sensitive usually but let's check repo implementation
            ->assertJsonMissing(['name' => 'Health']);
    }
}
