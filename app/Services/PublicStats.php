<?php

namespace App\Services;

use App\Models\User;

class PublicStats
{
    public function getTotalUsers()
    {
        $users = User::count();
        return $users;
    }
}
