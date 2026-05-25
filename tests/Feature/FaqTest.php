<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_faqs()
    {
        Faq::factory()->count(5)->create();

        $response = $this->getJson('/api/faqs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'question', 'answer', 'is_active', 'order_index', 'created_at', 'updated_at']
                ],
                'meta' => ['current_page', 'per_page', 'total'],
                'message' => []
            ]);
    }

    public function test_can_show_faq()
    {
        $faq = Faq::create([
            'question' => 'What is this?',
            'answer' => 'This is a test.',
            'is_active' => true,
            'order_index' => 1
        ]);

        $response = $this->getJson("/api/faqs/{$faq->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $faq->id,
                    'question' => 'What is this?',
                ],
                'message' => 'FAQ retrieved successfully'
            ]);
    }

    public function test_admin_can_create_faq()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'question' => 'New Question?',
            'answer' => 'New Answer.',
            'is_active' => true,
            'order_index' => 5
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/faqs', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'question' => 'New Question?',
                    'answer' => 'New Answer.',
                ],
                'message' => 'FAQ created successfully'
            ]);

        $this->assertDatabaseHas('faqs', ['question' => 'New Question?']);
    }

    public function test_admin_can_update_faq()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $faq = Faq::create([
            'question' => 'Old Question?',
            'answer' => 'Old Answer.',
            'is_active' => true,
            'order_index' => 1
        ]);

        $data = [
            'question' => 'Updated Question?',
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson("/api/admin/faqs/{$faq->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'question' => 'Updated Question?',
                ],
                'message' => 'FAQ updated successfully'
            ]);

        $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'question' => 'Updated Question?']);
    }

    public function test_admin_can_delete_faq()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $faq = Faq::create([
            'question' => 'To be deleted?',
            'answer' => 'Yes.',
            'is_active' => true,
            'order_index' => 1
        ]);

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson("/api/admin/faqs/{$faq->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'FAQ deleted successfully'
            ]);

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_unauthorized_user_cannot_create_faq()
    {
        $data = [
            'question' => 'Unauth Question?',
            'answer' => 'Unauth Answer.',
            'is_active' => true,
            'order_index' => 5
        ];

        $response = $this->postJson('/api/admin/faqs', $data);

        $response->assertStatus(401);
    }
}
