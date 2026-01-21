<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PlaintextContentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! \is_string($value)) {
            return;
        }

        $trimmedValue = trim($value);

        // Quick check for possible JSON objects or arrays
        if (! str_starts_with($trimmedValue, '{') && ! str_starts_with($trimmedValue, '[')) {
            return;
        }

        if (json_validate($trimmedValue)) {
            $fail('The :attribute must be plain text or markdown, not a raw JSON string.');
        }
    }
}
