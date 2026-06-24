<?php

namespace App\Services\Implementations;

use App\Models\Faq;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use App\Services\Interfaces\FaqServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class FaqService implements FaqServiceInterface
{
    protected FaqRepositoryInterface $faqRepository;

    public function __construct(FaqRepositoryInterface $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllFaqs(int $perPage): LengthAwarePaginator
    {
        return $this->faqRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getFaqById(int $id): Faq
    {
        $faq = $this->faqRepository->findById($id);

        if (!$faq) {
            Log::warning('FAQ lookup failed: FAQ not found', ['faq_id' => $id]);
            throw new ModelNotFoundException("FAQ with ID {$id} not found.");
        }

        return $faq;
    }

    /**
     * @inheritDoc
     */
    public function searchFaqs(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->faqRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function createFaq(array $data): Faq
    {
        $faq = $this->faqRepository->create($data);

        Log::info('FAQ created successfully', [
            'faq_id' => $faq->id,
            'question' => $faq->question,
        ]);

        return $faq;
    }

    /**
     * @inheritDoc
     */
    public function updateFaq(int $id, array $data): Faq
    {
        $faq = $this->faqRepository->update($id, $data);

        Log::info('FAQ updated successfully', [
            'faq_id' => $id,
            'question' => $faq->question,
        ]);

        return $faq;
    }

    /**
     * @inheritDoc
     */
    public function deleteFaq(int $id): bool
    {
        Log::info('FAQ deleted successfully', ['faq_id' => $id]);

        return $this->faqRepository->delete($id);
    }
}
