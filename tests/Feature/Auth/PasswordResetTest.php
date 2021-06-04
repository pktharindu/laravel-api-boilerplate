<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_email_is_required(): void
    {
        $this->postJson('forgot-password')
            ->assertJsonValidationErrors(['email' => 'required']);
    }

    public function test_email_must_be_valid(): void
    {
        $this->postJson('forgot-password', [
            'email' => 'not an email',
        ])
            ->assertJsonValidationErrors(['email' => 'email']);
    }

    public function test_email_must_exist(): void
    {
        $this->postJson('forgot-password', [
            'email' => 'taylor@laravel.com',
        ])
            ->assertJsonValidationErrors('email');
    }

    public function test_reset_link_can_be_successfully_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('forgot-password', [
            'email' => $user->email,
        ])
            ->assertOk()
            ->assertSee(__('passwords.sent'));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_token_is_required_to_reset(): void
    {
        $this->postJson('reset-password')
            ->assertJsonValidationErrors(['token' => 'required']);
    }

    public function test_token_must_be_valid_to_reset(): void
    {
        $user = User::factory()->create();

        $password = 'aVmjP9TzLx&ymf6e6rfHDo^A6';

        $this->postJson('reset-password', [
            'token' => Str::random(10),
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertJsonValidationErrors('email');
    }

    public function test_email_is_required_to_reset(): void
    {
        $this->postJson('reset-password')
            ->assertJsonValidationErrors(['email' => 'required']);
    }

    public function test_email_must_be_valid_to_reset(): void
    {
        $this->postJson('reset-password', [
            'email' => Str::random(10),
        ])
            ->assertJsonValidationErrors(['email' => 'email']);
    }

    public function test_password_is_required_to_reset(): void
    {
        $this->postJson('reset-password')
            ->assertJsonValidationErrors(['password' => 'required']);
    }

    public function test_password_can_be_reset_successfully(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make($originalPassword = 'secret'),
        ]);

        $token = Password::broker()->createToken($user);
        $password = Str::random(15);

        $this->postJson('reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertSuccessful();

        $user->refresh();

        self::assertFalse(Hash::check($originalPassword, $user->password));
        self::assertTrue(Hash::check($password, $user->password));
    }
}
