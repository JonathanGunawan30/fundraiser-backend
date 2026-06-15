<?php

namespace App\Services\Interfaces;

interface AuthServiceInterface
{
    /**
     * Authenticate an admin.
     *
     * @param array $credentials
     * @return array
     */
    public function adminLogin(array $credentials): array;

    /**
     * Request OTP for admin login.
     *
     * @param array $data
     * @return void
     */
    public function requestOtp(array $data): void;

    /**
     * Authenticate a user via OAuth.
     *
     * @param string $provider
     * @param mixed $socialUser
     * @return array
     */
    public function userSocialLogin(string $provider, mixed $socialUser): array;

    /**
     * Logout from the current session.
     *
     * @return void
     */
    public function logout(): void;
}
