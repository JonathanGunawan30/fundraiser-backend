<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\AuthServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserAuthController extends Controller
{
    use ApiResponse;

    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @return RedirectResponse
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(string $provider): \Illuminate\Http\RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $result = $this->authService->userSocialLogin($provider, $socialUser);

            $token = $result['token'];
            $user = $result['user'];

            $frontendUrl = config('app.frontend_url');

            $params = http_build_query([
                'token' => $token,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
                'role' => 'user'
            ]);

            return redirect()->to("{$frontendUrl}/auth/callback?{$params}");
        } catch (\Exception $e) {
            $frontendUrl = config('app.frontend_url');
            return redirect()->to("{$frontendUrl}/auth/login?error=" . urlencode($e->getMessage()));
        }
    }
}
