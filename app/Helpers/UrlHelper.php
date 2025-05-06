<?php

namespace App\Helpers;

class UrlHelper
{
    public function __construct()
    {
    }

    public static function getServerUrl()
    {
        return 'https://' . $_SERVER['HTTP_HOST'] . '/';
    }
}
