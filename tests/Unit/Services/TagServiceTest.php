<?php

namespace Tests\Unit\Services;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Implementations\TagService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class TagServiceTest extends MockeryTestCase
{
    protected $tagRepository;
    protected $tagService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. Create Mock of the Repository Interface
        $this->tagRepository = Mockery::mock(TagRepositoryInterface::class);
        
        // 2. Inject Mock into the Service
        $this->tagService = new TagService($this->tagRepository);
    }

    public function test_get_all_tags_returns_paginated_data()
    {
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->tagRepository->shouldReceive('getAllPaginated')
            ->once()
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->tagService->getAllTags($perPage);

        $this->assertSame($mockPaginator, $result);
    }

    public function test_get_tag_by_id_returns_tag_when_found()
    {
        $tagId = 1;
        $mockTag = new Tag(['id' => $tagId, 'name' => 'Test Tag']);

        $this->tagRepository->shouldReceive('findById')
            ->once()
            ->with($tagId)
            ->andReturn($mockTag);

        $result = $this->tagService->getTagById($tagId);

        $this->assertSame($mockTag, $result);
    }

    public function test_get_tag_by_id_throws_exception_when_not_found()
    {
        $tagId = 999;

        $this->tagRepository->shouldReceive('findById')
            ->once()
            ->with($tagId)
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Tag with ID {$tagId} not found.");

        $this->tagService->getTagById($tagId);
    }

    public function test_create_tag_calls_repository()
    {
        $data = ['name' => 'New Tag', 'slug' => 'new-tag'];
        $mockTag = new Tag($data);

        $this->tagRepository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($mockTag);

        $result = $this->tagService->createTag($data);

        $this->assertSame($mockTag, $result);
    }

    public function test_update_tag_calls_repository()
    {
        $tagId = 1;
        $data = ['name' => 'Updated Tag'];
        $mockTag = new Tag(array_merge(['id' => $tagId], $data));

        $this->tagRepository->shouldReceive('update')
            ->once()
            ->with($tagId, $data)
            ->andReturn($mockTag);

        $result = $this->tagService->updateTag($tagId, $data);

        $this->assertSame($mockTag, $result);
    }

    public function test_delete_tag_calls_repository()
    {
        $tagId = 1;

        $this->tagRepository->shouldReceive('delete')
            ->once()
            ->with($tagId)
            ->andReturn(true);

        $result = $this->tagService->deleteTag($tagId);

        $this->assertTrue($result);
    }

    public function test_search_tags_calls_repository()
    {
        $keyword = 'tech';
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->tagRepository->shouldReceive('search')
            ->once()
            ->with($keyword, $perPage)
            ->andReturn($mockPaginator);

        $result = $this->tagService->searchTags($keyword, $perPage);

        $this->assertSame($mockPaginator, $result);
    }
}
