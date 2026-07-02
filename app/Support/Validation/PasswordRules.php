<?php

namespace App\Support\Validation;

use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordRules
{
    public static function user(): PasswordRule
    {
        return PasswordRule::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols();
    }
}
