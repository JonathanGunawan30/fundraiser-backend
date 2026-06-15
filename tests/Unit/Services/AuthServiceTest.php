<?php

namespace Tests\Unit\Services;

use App\Models\Admin;
use App\Models\User;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Services\Implementations\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_logic()
    {
        $admin = new Admin([
            'email' => 'admin@test.com',
            'is_active' => true
        ]);
        $admin->id = 1;

        \Illuminate\Support\Facades\Redis::shouldReceive('get')
            ->once()
            ->with('otp:admin@test.com')
            ->andReturn('123456');

        \Illuminate\Support\Facades\Redis::shouldReceive('del')
            ->once()
            ->with('otp:admin@test.com');

        $this->mock(AuthRepositoryInterface::class, function (MockInterface $mock) use ($admin) {
            $mock->shouldReceive('findAdminByEmail')->with('admin@test.com')->andReturn($admin);
            $mock->shouldReceive('updateAdminLastLogin')->once();
        });

        $service = app(AuthService::class);
        $result = $service->adminLogin(['email' => 'admin@test.com', 'otp' => '123456']);

        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('admin@test.com', $result['admin']->email);
    }

    public function test_user_social_login_logic()
    {
        $socialUser = Mockery::mock('Laravel\Socialite\Two\User');
        $socialUser->shouldReceive('getId')->andReturn('123');
        $socialUser->shouldReceive('getName')->andReturn('John');
        $socialUser->shouldReceive('getEmail')->andReturn('john@test.com');
        $socialUser->shouldReceive('getAvatar')->andReturn('avatar.jpg');
        $socialUser->token = 'token';
        $socialUser->refreshToken = 'refresh';

        $user = new User(['email' => 'john@test.com']);
        $user->id = 1;

        $this->mock(AuthRepositoryInterface::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('findOrCreateUserByOauth')->once()->andReturn($user);
        });

        $service = app(AuthService::class);
        $result = $service->userSocialLogin('google', $socialUser);

        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('john@test.com', $result['user']->email);
    }
}
