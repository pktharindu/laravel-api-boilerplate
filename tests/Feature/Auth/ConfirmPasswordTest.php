<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConfirmPasswordTest extends TestCase
{
    public function test_it_fails_if_not_authenticated(): void
    {
        $this->postJson('user/confirm-password')
            ->assertUnauthorized();
    }

    public function test_password_required(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->postJson('user/confirm-password', [
            'password' => null,
        ])
            ->assertJsonValidationErrors('password');
    }

    public function test_it_fails_with_an_invalid_password(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->postJson('user/confirm-password', [
            'password' => 'invalid',
        ])
            ->assertJsonValidationErrors('password');
    }

    public function test_password_can_be_confirmed(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'password' => Hash::make($password = 'secret'),
        ]));

        $this->postJson('user/confirm-password', [
            'password' => $password,
        ])->assertCreated();
    }
}
