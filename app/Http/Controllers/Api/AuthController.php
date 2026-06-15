<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Services\Interfaces\AuthServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\AdminOtpRequest;

class AuthController extends Controller
{
    use ApiResponse;

    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Request OTP for admin login.
     *
     * @param AdminOtpRequest $request
     * @return JsonResponse
     */
    public function requestOtp(AdminOtpRequest $request): JsonResponse
    {
        $this->authService->requestOtp($request->validated());

        return $this->success(null, 'OTP sent successfully to your email');
    }

    /**
     * Admin login.
...
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function adminLogin(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->adminLogin($request->validated());

        return $this->success($result, 'Login successful');
    }

    /**
     * Logout.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->success(null, 'Logout successful');
    }
}
