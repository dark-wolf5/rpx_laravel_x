<?php

namespace App\Http\Controllers\Bugs;

use App\Http\Controllers\Controller;
use App\Models\Bugs;
use Illuminate\Http\Request;

class BugsController extends Controller
{
    public function insert(Request $bugRequest, Bugs $bugs)
    {
        return $bugs->insert($bugRequest);
    }
}
