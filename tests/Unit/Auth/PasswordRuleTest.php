<?php

namespace Tests\Unit\Auth;

use Illuminate\Support\Str;
use Laravel\Fortify\Rules\Password;
use Tests\TestCase;

class PasswordRuleTest extends TestCase
{
    public function test_password_rule(): void
    {
        $rule = new Password;

        self::assertTrue($rule->passes('password', 'password'));
        self::assertTrue($rule->passes('password', 234234234));
        self::assertFalse($rule->passes('password', ['foo' => 'bar']));
        self::assertFalse($rule->passes('password', 'secret'));

        self::assertTrue(Str::contains($rule->message(), 'must be at least 8 characters'));

        $rule->length(10);

        self::assertFalse($rule->passes('password', 'password'));
        self::assertTrue($rule->passes('password', 'password11'));

        self::assertTrue(Str::contains($rule->message(), 'must be at least 10 characters'));

        $rule->length(8)->requireUppercase();

        self::assertFalse($rule->passes('password', 'password'));
        self::assertTrue($rule->passes('password', 'Password'));

        self::assertTrue(Str::contains($rule->message(), 'characters and contain at least one uppercase character'));

        $rule->length(8)->requireNumeric();

        self::assertFalse($rule->passes('password', 'Password'));
        self::assertFalse($rule->passes('password', 'password1'));
        self::assertTrue($rule->passes('password', 'Password1'));

        self::assertTrue(Str::contains($rule->message(), 'characters and contain at least one uppercase character and one number'));
    }

    public function test_password_rule_can_require_special_characters(): void
    {
        $rule = new Password;

        $rule->length(8)->requireSpecialCharacter();

        self::assertTrue($rule->passes('password', 'password!'));
        self::assertFalse($rule->passes('password', 'password'));

        self::assertTrue(Str::contains($rule->message(), 'must be at least 8 characters'));
        self::assertTrue(Str::contains($rule->message(), 'special character'));
    }

    public function test_password_rule_can_require_numeric_and_special_characters(): void
    {
        $rule = new Password;

        $rule->length(10)->requireNumeric()->requireSpecialCharacter();

        self::assertTrue($rule->passes('password', 'password5%'));
        self::assertFalse($rule->passes('password', 'my-password'));

        self::assertTrue(Str::contains($rule->message(), 'must be at least 10 characters'));
        self::assertTrue(Str::contains($rule->message(), 'contain at least one special character'));
        self::assertTrue(Str::contains($rule->message(), 'and one number'));
    }
}
