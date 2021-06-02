<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_email_is_required(): void
    {
        $this->postJson('login')
            ->assertJsonValidationErrors(['email' => 'required']);
    }

    public function test_password_is_required(): void
    {
        $this->postJson('login')
            ->assertJsonValidationErrors(['password' => 'required']);
    }

    public function test_validation_exception_returned_on_failure(): void
    {
        $this->postJson('login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ])->assertJsonValidationErrors(['email']);
    }

    public function test_login_attempts_are_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', [
                'email' => 'taylor@laravel.com',
                'password' => 'secret',
            ]);
        }

        $this->postJson('/login', [
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ])->assertStatus(429);
    }

    public function test_user_can_authenticate(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make($password = 'secret'),
        ]);

        $this->postJson('login', [
            'email' => $user->email,
            'password' => $password,
        ])->assertOk();
    }

    public function test_the_user_can_logout_of_the_application(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->postJson('/logout')
            ->assertStatus(204);
    }
}
