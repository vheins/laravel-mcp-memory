<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\MemoryType;
use BackedEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ImmutableTypeRule implements ValidationRule
{
    /**
     * @var string[]
     */
    protected array $restrictedTypes = [
        MemoryType::SystemConstraint->value,
        MemoryType::BusinessRule->value,
    ];

    public function __construct(protected string $actorType = 'human') {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=):PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $typeValue = $value instanceof BackedEnum ? $value->value : $value;

        if ($this->actorType === 'ai' && \in_array($typeValue, $this->restrictedTypes)) {
            $fail('AI agents cannot create or modify memories of type: ' . $typeValue);
        }
    }
}
