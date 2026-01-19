<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImmutableTypeRule implements ValidationRule
{
    protected array $restrictedTypes = ['system_constraint', 'business_rule'];

    public function __construct(protected string $actorType = 'human')
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->actorType === 'ai' && in_array($value, $this->restrictedTypes)) {
            $fail('AI agents cannot create or modify memories of type: ' . $value);
        }
    }
}
