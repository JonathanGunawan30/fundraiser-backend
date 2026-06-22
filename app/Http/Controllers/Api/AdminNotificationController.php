<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the admin's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $admin = Auth::guard('admin-api')->user();
        $perPage = $request->query('per_page', 10);
        
        $notifications = $admin->notifications()->paginate($perPage);

        return $this->successWithPagination($notifications, 'Admin notifications retrieved successfully');
    }

    /**
     * Display a listing of unread notifications.
     */
    public function unread(Request $request): JsonResponse
    {
        $admin = Auth::guard('admin-api')->user();
        $perPage = $request->query('per_page', 10);
        
        $notifications = $admin->unreadNotifications()->paginate($perPage);

        return $this->successWithPagination($notifications, 'Admin unread notifications retrieved successfully');
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $admin = Auth::guard('admin-api')->user();
        $notification = $admin->notifications()->where('id', $id)->first();

        if (!$notification) {
            return $this->error('Notification not found.', 404);
        }

        $notification->markAsRead();

        return $this->success(null, 'Admin notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $admin = Auth::guard('admin-api')->user();
        $admin->unreadNotifications->markAsRead();

        return $this->success(null, 'All admin notifications marked as read');
    }
}
