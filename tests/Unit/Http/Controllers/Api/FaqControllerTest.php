<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\Faq;
use App\Services\Interfaces\FaqServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Tests\TestCase;

class FaqControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_success_response()
    {
        $this->mock(FaqServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('getAllFaqs')->once()->with(10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/faqs?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'FAQs retrieved successfully'
            ]);
    }

    public function test_show_returns_success_response()
    {
        $faq = new Faq(['question' => 'Test Question', 'answer' => 'Test Answer']);
        $faq->id = 1;

        $this->mock(FaqServiceInterface::class, function (MockInterface $mock) use ($faq) {
            $mock->shouldReceive('getFaqById')->once()->with(1)->andReturn($faq);
        });

        $response = $this->getJson('/api/faqs/1');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'question' => 'Test Question'
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
        $faqData = ['question' => 'New Question?', 'answer' => 'New Answer.', 'is_active' => true, 'order_index' => 1];
        $faq = new Faq(array_merge(['id' => 1], $faqData));
        $faq->id = 1;

        $this->mock(FaqServiceInterface::class, function (MockInterface $mock) use ($faq, $faqData) {
            $mock->shouldReceive('createFaq')->once()->with($faqData)->andReturn($faq);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/faqs', $faqData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'FAQ created successfully',
                'data' => ['question' => 'New Question?']
            ]);
    }

    public function test_update_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $faqData = ['question' => 'Updated Question?'];
        $faq = new Faq(['question' => 'Updated Question?', 'answer' => 'Old Answer']);
        $faq->id = 1;

        $this->mock(FaqServiceInterface::class, function (MockInterface $mock) use ($faq, $faqData) {
            $mock->shouldReceive('updateFaq')->once()->with(1, $faqData)->andReturn($faq);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson('/api/admin/faqs/1', $faqData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'FAQ updated successfully',
                'data' => ['question' => 'Updated Question?']
            ]);
    }

    public function test_destroy_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->mock(FaqServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteFaq')->once()->with(1)->andReturn(true);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson('/api/admin/faqs/1');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'FAQ deleted successfully'
            ]);
    }

    public function test_search_returns_success_response()
    {
        $this->mock(FaqServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('searchFaqs')->once()->with('test', 10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/faqs/search?keyword=test&per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'FAQs search results retrieved successfully'
            ]);
    }
}
