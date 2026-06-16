<?php

namespace App\Repositories\Implementations;

use App\Models\User;
use App\Models\Admin;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAdminByEmail(string $email): ?Admin
    {
        return Admin::where('email', $email)->first();
    }

    /**
     * @inheritDoc
     */
    public function findOrCreateUserByOauth(array $oauthData): User
    {
        return DB::transaction(function () use ($oauthData) {
            // 1. Check for existing OAuth account
            $oauthAccount = \App\Models\OauthAccount::where('provider', $oauthData['provider'])
                ->where('provider_id', $oauthData['provider_id'])
                ->first();

            if ($oauthAccount) {
                $user = $oauthAccount->user;
                // Update user info if changed
                $user->update([
                    'name' => $oauthData['name'] ?? $user->name,
                    'avatar_url' => $oauthData['avatar_url'] ?? $user->avatar_url,
                ]);
            } else {
                // 2. Handle missing email by generating a unique placeholder if necessary
                $email = $oauthData['email'] ?? ($oauthData['provider_id'] . '@' . $oauthData['provider'] . '.placeholder.com');

                // 3. Fallback to check user by email
                $user = User::where('email', $email)->first();

                if (!$user) {
                    // 4. Create new user
                    $user = User::create([
                        'name' => $oauthData['name'] ?? ('User_' . $oauthData['provider_id']),
                        'email' => $email,
                        'avatar_url' => $oauthData['avatar_url'] ?? null,
                        'status' => 'active',
                    ]);
                } else {
                    // Update existing user with new info
                    $user->update([
                        'name' => $oauthData['name'] ?? $user->name,
                        'avatar_url' => $oauthData['avatar_url'] ?? $user->avatar_url,
                    ]);
                }
            }

            // 5. Update or create the OAuth account record linked to the user
            $user->oauthAccounts()->updateOrCreate(
                [
                    'provider' => $oauthData['provider'],
                    'provider_id' => $oauthData['provider_id'],
                ],
                [
                    'access_token' => $oauthData['access_token'] ?? null,
                    'refresh_token' => $oauthData['refresh_token'] ?? null,
                    'token_expires_at' => $oauthData['token_expires_at'] ?? null,
                ]
            );

            return $user;
        });
    }

    /**
     * @inheritDoc
     */
    public function updateAdminLastLogin(Admin $admin): void
    {
        $admin->update([
            'last_login_at' => now(),
        ]);
    }
}
