<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\AuthServiceInterface;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Redis;
use App\Mail\AdminOtpMail;
use App\Jobs\SendAdminOtpJob;
use Illuminate\Support\Facades\Log;

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
            Log::warning('Admin login failed: Email not registered', ['email' => $credentials['email']]);
            abort(401, 'Invalid credentials.');
        }

        if (!$admin->is_active) {
            Log::warning('Admin login failed: Account is inactive', ['email' => $credentials['email'], 'admin_id' => $admin->id]);
            abort(401, 'Account is inactive.');
        }

        $otp = Redis::get('otp:' . $credentials['email']);

        if (!$otp || $otp !== $credentials['otp']) {
            Log::warning('Admin login failed: Invalid or expired OTP', ['email' => $credentials['email'], 'admin_id' => $admin->id]);
            abort(422, 'Invalid or expired OTP.');
        }

        // Invalidate OTP after successful use
        Redis::del('otp:' . $credentials['email']);

        $this->authRepository->updateAdminLastLogin($admin);

        $token = Auth::guard('admin-api')->login($admin);

        Log::info('Admin login successful', ['admin_id' => $admin->id, 'email' => $admin->email]);

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

        if (!$admin || !$admin->is_active) {
            Log::warning('Admin OTP request failed: Email not registered or inactive', ['email' => $data['email']]);
            // Return early to prevent email enumeration
            return;
        }

        $otp = (string) rand(100000, 999999);

        // Store OTP in Redis with 5 minutes TTL
        Redis::setex('otp:' . $data['email'], 300, $otp);

        // Send OTP via email
        SendAdminOtpJob::dispatch($admin->email, $otp);

        Log::info('Admin OTP generated and sent successfully', ['email' => $data['email'], 'admin_id' => $admin->id]);
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

        Log::info('User social login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'provider' => $provider
        ]);

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
        $user = Auth::user();
        if ($user) {
            Log::info('User logged out', ['user_id' => $user->id, 'role' => Auth::getDefaultDriver()]);
        }
        Auth::logout();
    }
}
