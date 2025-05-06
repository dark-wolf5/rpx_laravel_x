<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    private $error_message;

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
    public function passes($attribute, $password)
    {
        if (strlen($password) > 135)
        {
            $this->error_message = 'wrongLength';
            return false;
        }

        if (strlen($password) < 8)
        {
            $this->error_message = 'wrongLength';
            return false;
        }

        $re = '/[0-9]/';
        if (!preg_match($re, $password))
        {
            $this->error_message = 'oneNumber';
            return false;
        }

        $re = '/[a-z]/';
        if (!preg_match($re, $password))
        {
            $this->error_message = 'oneLowerCase';
            return false;
        }

        $re = '/[A-Z]/';
        if (!preg_match($re, $password))
        {
            $this->error_message = 'oneUpperCase';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error_message;
    }
}
