<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\AuthServiceInterface;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminOtpMail;

class AuthService implements AuthServiceInterface
{
    protected AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * @inheritDoc
     */
    public function adminLogin(array $credentials): array
    {
        $admin = $this->authRepository->findAdminByEmail($credentials['email']);

        if (!$admin) {
            abort(401, 'Invalid credentials.');
        }

        if (!$admin->is_active) {
            abort(401, 'Account is inactive.');
        }

        $otp = Redis::get('otp:' . $credentials['email']);

        if (!$otp || $otp !== $credentials['otp']) {
            abort(422, 'Invalid or expired OTP.');
        }

        // Invalidate OTP after successful use
        Redis::del('otp:' . $credentials['email']);

        $this->authRepository->updateAdminLastLogin($admin);

        $token = Auth::guard('admin-api')->login($admin);

        return [
            'admin' => $admin,
            'token' => $token,
        ];
    }

    /**
     * @inheritDoc
     */
    public function requestOtp(array $data): void
    {
        $admin = $this->authRepository->findAdminByEmail($data['email']);

        if (!$admin) {
            // We return early to avoid email enumeration, but in some cases you might want to return an error.
            // However, based on the requirements, we'll just check if admin exists.
            abort(404, 'Admin not found.');
        }

        if (!$admin->is_active) {
            abort(401, 'Account is inactive.');
        }

        $otp = (string) rand(100000, 999999);

        // Store OTP in Redis with 5 minutes TTL
        Redis::setex('otp:' . $data['email'], 300, $otp);

        // Send OTP via email
        Mail::to($admin->email)->send(new AdminOtpMail($otp));
    }

    /**
     * @inheritDoc
     */
    public function userSocialLogin(string $provider, mixed $socialUser): array
    {
        $oauthData = [
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'avatar_url' => $socialUser->getAvatar(),
            'access_token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken,
            'token_expires_at' => property_exists($socialUser, 'expiresIn') ? now()->addSeconds($socialUser->expiresIn) : null,
        ];

        $user = $this->authRepository->findOrCreateUserByOauth($oauthData);

        $token = Auth::guard('api')->login($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @inheritDoc
     */
    public function logout(): void
    {
        Auth::logout();
    }
}
