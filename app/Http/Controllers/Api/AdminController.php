<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAdminProfileRequest;
use App\Http\Requests\Api\UpdateAdminPasswordRequest;
use App\Http\Resources\AdminResource;
use App\Services\Interfaces\AdminServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminController extends Controller
{
    use ApiResponse;

    protected AdminServiceInterface $adminService;

    public function __construct(AdminServiceInterface $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $admins = $this->adminService->getAllAdmins($perPage);

        return $this->successWithPagination($admins, 'Admins retrieved successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $admin = $this->adminService->getAdminById($id);
            return $this->success($admin, 'Admin retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Search for resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->query('keyword', '');
        $perPage = $request->query('per_page', 10);
        
        $admins = $this->adminService->searchAdmins($keyword, $perPage);

        return $this->successWithPagination($admins, 'Admins search results retrieved successfully');
    }

    /**
     * Get current admin profile.
     *
     * @return JsonResponse
     */
    public function profile(): JsonResponse
    {
        $admin = Auth::guard('admin-api')->user();
        return $this->success(new AdminResource($admin), 'Admin profile retrieved successfully');
    }

    /**
     * Update admin profile.
     *
     * @param UpdateAdminProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateAdminProfileRequest $request): JsonResponse
    {
        $adminId = Auth::guard('admin-api')->id();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar');
        }

        $admin = $this->adminService->updateProfile($adminId, $data);

        return $this->success(new AdminResource($admin), 'Profile updated successfully');
    }

    /**
     * Update admin password.
     *
     * @param UpdateAdminPasswordRequest $request
     * @return JsonResponse
     */
    public function updatePassword(UpdateAdminPasswordRequest $request): JsonResponse
    {
        $adminId = Auth::guard('admin-api')->id();
        $data = $request->validated();

        $this->adminService->updatePassword($adminId, $data['current_password'], $data['password']);

        return $this->success(null, 'Password updated successfully');
    }
}
