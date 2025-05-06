<?php

namespace App\Http\Controllers\UserLocation;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param \App\Models\UserLocation $userLocation
     *
     * @return \Illuminate\Http\Response
     */
    public function show(UserLocation $userLocation)
    {
        return $userLocation->getMyLocation();
    }

    public function saveCurrentLocation(Request $request, UserLocation $userLocation)
    {
        return $userLocation->saveCurrentLocation($request);
    }

    public function retrieveSurroundings(UserLocation $userLocation, Request $request)
    {
        return $userLocation->retrieveSurroundings($request);
    }
}
