<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LastName implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $rexSafety = '/^[a-zA-Z]+$/';

        return preg_match($rexSafety, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'invalidInput';
    }
}
