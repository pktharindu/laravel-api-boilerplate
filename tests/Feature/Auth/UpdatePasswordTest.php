<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    public function test_it_fails_if_not_authenticated(): void
    {
        $this->putJson('user/password')
            ->assertUnauthorized();
    }

    public function test_it_requires_current_password(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/password')
            ->assertJsonValidationErrors(['current_password' => 'required']);
    }

    public function test_current_password_must_be_valid(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/password', [
            'current_password' => 'invalid',
        ])
            ->assertJsonValidationErrors('current_password');
    }

    public function test_it_requires_password(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/password')
            ->assertJsonValidationErrors(['password' => 'required']);
    }

    public function test_password_and_password_confirmation_must_match(): void
    {
        Sanctum::actingAs(
            User::factory()->create([
                'password' => Hash::make($password = 'secret'),
            ])
        );

        $this->putJson('user/password', [
            'current_password' => $password,
            'password' => Str::random(15),
            'password_confirmation' => Str::random(10),
        ])
            ->assertJsonValidationErrors('password');
    }

    public function test_it_can_update_password(): void
    {
        Sanctum::actingAs(
            User::factory()->create([
                'password' => Hash::make($oldPassword = 'secret'),
            ])
        );

        $password = Str::random(15);

        $this->putJson('user/password', [
            'current_password' => $oldPassword,
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertOk();
    }
}
