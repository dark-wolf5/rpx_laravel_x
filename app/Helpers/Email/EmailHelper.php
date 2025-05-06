<?php

namespace App\Helpers\Email;

class EmailHelper
{
    public function __construct(public string $emailLocale)
    {
    }

    public function getPromotionalEmailText(string $firstName, string $businessName, string $body): string
    {
        return trans('promotional_email.promotional_text', ['firstName' => $firstName, 'businessName' => $businessName, 'body' => $body]);
    }
}
