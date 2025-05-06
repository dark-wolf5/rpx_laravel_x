<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MeetUpInvitationController extends Controller
{
    public function handle(Request $request)
    {
        // Process webhook payload
        // Perform actions based on the webhook data

        return response()->json(['success' => true]);
    }
}
