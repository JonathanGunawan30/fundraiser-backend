<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use App\Mail\AdminOtpMail;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
    }

    public function test_admin_can_request_otp()
    {
        Mail::fake();
        Redis::shouldReceive('setex')
            ->once()
            ->withArgs(function ($key, $ttl, $value) {
                return $key === 'otp:admin@example.com' && $ttl === 300 && strlen($value) === 6;
            });

        $response = $this->postJson('/api/auth/otp', [
            'email' => 'admin@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'OTP sent successfully to your email',
            ]);

        Mail::assertSent(AdminOtpMail::class, function ($mail) {
            return $mail->hasTo('admin@example.com');
        });
    }

    public function test_admin_cannot_request_otp_with_invalid_email()
    {
        $response = $this->postJson('/api/auth/otp', [
            'email' => 'notfound@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_login_with_valid_otp()
    {
        Redis::shouldReceive('get')
            ->once()
            ->with('otp:admin@example.com')
            ->andReturn('123456');

        Redis::shouldReceive('del')
            ->once()
            ->with('otp:admin@example.com');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'admin',
                    'token',
                ],
                'message',
            ]);
    }

    public function test_admin_cannot_login_with_invalid_otp()
    {
        Redis::shouldReceive('get')
            ->once()
            ->with('otp:admin@example.com')
            ->andReturn('123456');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'otp' => '000000',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid or expired OTP.',
            ]);
    }

    public function test_admin_cannot_login_with_expired_otp()
    {
        Redis::shouldReceive('get')
            ->once()
            ->with('otp:admin@example.com')
            ->andReturn(null);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid or expired OTP.',
            ]);
    }
}
