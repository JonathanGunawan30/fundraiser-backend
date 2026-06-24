<?php

namespace App\Services\Implementations;

use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Services\Interfaces\NotificationServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    protected NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllNotifications(int $perPage): LengthAwarePaginator
    {
        return $this->notificationRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getNotificationById(int $id): Notification
    {
        $notification = $this->notificationRepository->findById($id);

        if (!$notification) {
            Log::warning('Notification lookup failed: Notification not found', ['notification_id' => $id]);
            throw new ModelNotFoundException("Notification with ID {$id} not found.");
        }

        return $notification;
    }

    /**
     * @inheritDoc
     */
    public function searchNotifications(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->notificationRepository->search($keyword, $perPage);
    }
}
