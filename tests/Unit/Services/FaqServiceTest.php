<?php

namespace Tests\Unit\Services;

use App\Models\Faq;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use App\Services\Implementations\FaqService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FaqServiceTest extends MockeryTestCase
{
    protected $faqRepository;
    protected $faqService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->faqRepository = Mockery::mock(FaqRepositoryInterface::class);
        $this->faqService = new FaqService($this->faqRepository);
    }

    public function test_get_all_faqs_returns_paginated_data()
    {
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->faqRepository->shouldReceive('getAllPaginated')
            ->once()
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->faqService->getAllFaqs($perPage);

        $this->assertSame($mockPaginator, $result);
    }

    public function test_get_faq_by_id_returns_faq_when_found()
    {
        $faqId = 1;
        $mockFaq = new Faq(['id' => $faqId, 'question' => 'What is this?']);

        $this->faqRepository->shouldReceive('findById')
            ->once()
            ->with($faqId)
            ->andReturn($mockFaq);

        $result = $this->faqService->getFaqById($faqId);

        $this->assertSame($mockFaq, $result);
    }

    public function test_get_faq_by_id_throws_exception_when_not_found()
    {
        $faqId = 999;

        $this->faqRepository->shouldReceive('findById')
            ->once()
            ->with($faqId)
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("FAQ with ID {$faqId} not found.");

        $this->faqService->getFaqById($faqId);
    }

    public function test_create_faq_calls_repository()
    {
        $data = ['question' => 'How to donate?', 'answer' => 'Follow the guide.'];
        $mockFaq = new Faq($data);

        $this->faqRepository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($mockFaq);

        $result = $this->faqService->createFaq($data);

        $this->assertSame($mockFaq, $result);
    }

    public function test_update_faq_calls_repository()
    {
        $faqId = 1;
        $data = ['question' => 'Updated Question'];
        $mockFaq = new Faq(array_merge(['id' => $faqId], $data));

        $this->faqRepository->shouldReceive('update')
            ->once()
            ->with($faqId, $data)
            ->andReturn($mockFaq);

        $result = $this->faqService->updateFaq($faqId, $data);

        $this->assertSame($mockFaq, $result);
    }

    public function test_delete_faq_calls_repository()
    {
        $faqId = 1;

        $this->faqRepository->shouldReceive('delete')
            ->once()
            ->with($faqId)
            ->andReturn(true);

        $result = $this->faqService->deleteFaq($faqId);

        $this->assertTrue($result);
    }

    public function test_search_faqs_calls_repository()
    {
        $keyword = 'donate';
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->faqRepository->shouldReceive('search')
            ->once()
            ->with($keyword, $perPage)
            ->andReturn($mockPaginator);

        $result = $this->faqService->searchFaqs($keyword, $perPage);

        $this->assertSame($mockPaginator, $result);
    }
}
