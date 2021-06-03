<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateProfileInformationTest extends TestCase
{
    public function test_it_fails_if_not_authenticated(): void
    {
        $this->putJson('user/profile-information')
            ->assertUnauthorized();
    }

    public function test_name_is_required(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information')
            ->assertJsonValidationErrors(['name' => 'required']);
    }

    public function test_name_is_a_string(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information', [
            'name' => 1,
        ])
            ->assertJsonValidationErrors(['name' => 'string']);
    }

    public function test_name_has_a_max_characters(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information', [
            'name' => Str::random(256),
        ])
            ->assertJsonValidationErrors('name');
    }

    public function test_email_is_required(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information')
            ->assertJsonValidationErrors(['email' => 'required']);
    }

    public function test_email_is_a_string(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information', [
            'email' => 1,
        ])
            ->assertJsonValidationErrors(['email' => 'string']);
    }

    public function test_email_is_valid(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information', [
            'email' => 'invalid',
        ])
            ->assertJsonValidationErrors(['email' => 'email']);
    }

    public function test_email_has_a_max_characters(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information', [
            'email' => Str::random(256).'@gmail.com',
        ])
            ->assertJsonValidationErrors('email');
    }

    public function test_email_must_be_unique(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information', [
            'email' => $user->email,
        ])
            ->assertJsonValidationErrors(['email' => Rule::unique('users')]);
    }

    public function test_it_can_update_profile_information(): void
    {
        $dummyUser = User::factory()->make();

        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->putJson('user/profile-information', [
            'name' => $dummyUser->name,
            'email' => $dummyUser->email,
        ])->assertOk();
    }
}
