<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Bugs extends Model
{
    public function insert(Request $bugRequest)
    {
        $validatedData = $bugRequest->validate([
            'description' => 'required|string|max:500|min:1',
        ]);

        $bug = new Bugs();

        $bug->ip_address = $bugRequest->ip();
        $bug->description = $bugRequest->description;

        $bug->save();

        $response = [
            'success' => true,
        ];

        return response($response);
    }
}
