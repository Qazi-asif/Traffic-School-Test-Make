<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CitationNumber implements Rule
{
    private $reasonAttending;

    public function __construct($reasonAttending = null)
    {
        $this->reasonAttending = $reasonAttending;
    }

    public function passes($attribute, $value)
    {
        // Citation number required for BDI School Election (B1)
        if ($this->reasonAttending === 'B1') {
            // Must be exactly 7 characters
            return strlen($value) === 7;
        }

        // For other reasons, if provided, can be any length except 7 (treated as court order)
        if (! empty($value)) {
            return true;
        }

        return true;
    }

    public function message()
    {
        if ($this->reasonAttending === 'B1') {
            return 'The citation number must be exactly 7 characters for BDI School Election.';
        }

        return 'The citation number format is invalid.';
    }
}
