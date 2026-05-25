<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', 10);
        
        $notifications = $user->notifications()->paginate($perPage);

        return $this->successWithPagination($notifications, 'Notifications retrieved successfully');
    }

    /**
     * Display a listing of unread notifications.
     */
    public function unread(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', 10);
        
        $notifications = $user->unreadNotifications()->paginate($perPage);

        return $this->successWithPagination($notifications, 'Unread notifications retrieved successfully');
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return $this->error('Notification not found.', 404);
        }

        $notification->markAsRead();

        return $this->success(null, 'Notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return $this->success(null, 'All notifications marked as read');
    }
}
