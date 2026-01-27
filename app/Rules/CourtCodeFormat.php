<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CourtCodeFormat implements ValidationRule
{
    public function __construct(protected string $codeType) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = match ($this->codeType) {
            'tvcc' => '/^[A-Z]{2}\d{3,6}$/',
            'court_id' => '/^[A-Z0-9]{4,10}$/',
            'location_code' => '/^\d{3,5}$/',
            'branch_code' => '/^[A-Z0-9]{2,8}$/',
            'state_code' => '/^[A-Z]{2}$/',
            default => '/^[A-Z0-9]{1,50}$/',
        };

        if (! preg_match($pattern, $value)) {
            $fail("The {$attribute} format is invalid for type {$this->codeType}.");
        }
    }
}
