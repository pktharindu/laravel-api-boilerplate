<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    public function test_it_fails_if_not_authenticated(): void
    {
        $this->postJson('email/verification-notification')
            ->assertUnauthorized();
    }

    public function test_it_does_not_sends_email_verification_notification_verified_users(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->postJson('email/verification-notification')
            ->assertNoContent();
    }

    public function test_it_sends_email_verification_notification(): void
    {
        Sanctum::actingAs(
            User::factory()->create([
                'email_verified_at' => null,
            ])
        );

        $this->postJson('email/verification-notification')
            ->assertStatus(202);
    }

    public function test_verification_fails_if_not_authenticated(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        self::assertNull($user->email_verified_at);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->getJson($verificationUrl)
            ->assertUnauthorized();

        $user->refresh();

        self::assertNull($user->email_verified_at);
    }

    public function test_an_user_can_verify_his_email_address(): void
    {
        Sanctum::actingAs(
            $user = User::factory()->create([
                'email_verified_at' => null,
            ])
        );

        self::assertNull($user->email_verified_at);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->getJson($verificationUrl)
            ->assertStatus(202);

        $user->refresh();

        self::assertNotNull($user->email_verified_at);
    }
}
