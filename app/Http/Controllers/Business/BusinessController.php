<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function verify(Request $request, Business $business)
    {
        return $business->verify($request);
    }

    public function uploadPhoto(Request $request, Business $business)
    {
        return $business->uploadPhoto($request);
    }

    public function saveBusiness(Request $request, Business $business)
    {
        return $business->saveBusiness($request);
    }

    public function getGooglePlacesToEat(Request $request, Business $business)
    {
        return $business->getGooglePlacesToEat($request);
    }

    public function show(Request $request, Business $business)
    {
        return $business->show($request);
    }

    public function saveLocation(Request $request, Business $business)
    {
        return $business->saveLocation($request);
    }
}
