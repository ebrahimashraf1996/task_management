<?php

namespace App\Rules\Auth;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class PasswordRule implements ValidationRule
{
    public function __construct(
        public ?string $email
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->email) {
            return;
        }

        $user = User::whereEmail($this->email)->first();

        if (! $user || ! Hash::check($value, $user->password)) {
            $fail('These Credentials Do Not Match Our Records');
        }
    }
}
