<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FloridaDriverLicense implements Rule
{
    private $stateOfRecord;

    public function __construct($stateOfRecord = 'FL')
    {
        $this->stateOfRecord = $stateOfRecord;
    }

    public function passes($attribute, $value)
    {
        // If state is Florida, must match Florida format: A999999999999
        if (strtoupper($this->stateOfRecord) === 'FL') {
            return preg_match('/^[A-Z]\d{12}$/', $value);
        }

        // For out-of-state licenses, just check it's not empty
        return ! empty($value);
    }

    public function message()
    {
        if (strtoupper($this->stateOfRecord) === 'FL') {
            return 'The Florida driver license must be in format: A999999999999 (one letter followed by 12 digits).';
        }

        return 'The driver license number is required.';
    }
}
