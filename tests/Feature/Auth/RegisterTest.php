<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function test_name_is_required(): void
    {
        $this->postJson('register')
            ->assertJsonValidationErrors(['name' => 'required']);
    }

    public function test_name_is_a_string(): void
    {
        $this->postJson('register', [
            'name' => 1,
        ])
            ->assertJsonValidationErrors(['name' => 'string']);
    }

    public function test_name_has_max(): void
    {
        $this->postJson('register', [
            'name' => Str::random(256),
        ])->assertJsonValidationErrors('name');
    }

    public function test_email_is_required(): void
    {
        $this->postJson('register')
            ->assertJsonValidationErrors(['email' => 'required']);
    }

    public function test_email_is_a_string(): void
    {
        $this->postJson('register', [
            'email' => 1,
        ])
            ->assertJsonValidationErrors(['email' => 'string']);
    }

    public function test_email_must_be_valid(): void
    {
        $this->postJson('register', [
            'email' => 'not an email',
        ])
            ->assertJsonValidationErrors(['email' => 'email']);
    }

    public function test_email_has_max(): void
    {
        $this->postJson('register', [
            'email' => Str::random(256).'@gmail.com',
        ])->assertJsonValidationErrors('email');
    }

    public function test_email_is_unique(): void
    {
        $user = User::factory()->create();

        $this->postJson('register', [
            'email' => $user->email,
        ])->assertJsonValidationErrors(['email' => Rule::unique(User::class)]);
    }

    public function test_password_is_required(): void
    {
        $this->postJson('register')
            ->assertJsonValidationErrors(['password' => 'required']);
    }

    public function test_password_is_a_string(): void
    {
        $this->postJson('register', [
            'password' => 1,
        ])
            ->assertJsonValidationErrors(['password' => 'string']);
    }

    public function test_password_has_min_characters(): void
    {
        $this->postJson('register', [
            'password' => 'a',
        ])
            ->assertJsonValidationErrors('password');
    }

    public function test_password_is_confirmed(): void
    {
        $this->postJson('register', [
            'password' => Str::random(15),
        ])
            ->assertJsonValidationErrors('password');
    }

    public function test_password_has_max(): void
    {
        $this->postJson('register', [
            'password' => Str::random(256),
        ])->assertJsonValidationErrors('password');
    }

    public function test_user_can_register(): void
    {
        Event::fake();

        $user = User::factory()->make([
            'password' => $password = Str::random(15),
        ]);

        $this->postJson('register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertCreated();

        Event::assertDispatched(Registered::class);
    }
}
