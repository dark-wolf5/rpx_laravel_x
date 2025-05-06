<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\PromoterDeviceAlternator;
use Illuminate\Http\Request;

class PromoterDeviceAlternatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function retrieveBusinessList(Request $request)
    {
        $requestValidated = $request->validate([
            'device_id' => 'string|required|max:65'
        ]);

        $businessList = PromoterDeviceAlternator::where('device_id', $requestValidated['device_id'])
            ->first()['business_list'];
        $businessIdList = explode(",", $businessList);

        $finalBusinessList = Business::whereIn('id', $businessIdList)
            ->has('rewards')
            ->with('loyaltyTiers')
            ->with('rpxUser')
            ->with('rewards')
            ->inRandomOrder()
            ->get();

        return response([
            'business_list' => $finalBusinessList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDeviceLocation(Request $request)
    {
        $requestValidated = $request->validate([
            'id' => 'required|string|max:11',
            'loc_x'       => 'required|max:90|min:-90|numeric',
            'loc_y'       => 'required|max:180|min:-180|numeric',
        ]);

        $alternatorRecord = PromoterDeviceAlternator::where('device_id', $requestValidated['id'])->frist();
        PromoterDeviceAlternator::update([
            'loc_x' => $requestValidated['loc_x'],
            'loc_y' => $requestValidated['loc_y'],
        ]);

        $alternatorRecord->refresh();

        return response([
            'new_tablet_location' => $alternatorRecord->loc_x . "," . $alternatorRecord->loc_y,
            'updated_at' => $alternatorRecord->updated_at
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PromoterDeviceAlternator  $promoterDeviceAlternator
     * @return \Illuminate\Http\Response
     */
    public function show(PromoterDeviceAlternator $promoterDeviceAlternator)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PromoterDeviceAlternator  $promoterDeviceAlternator
     * @return \Illuminate\Http\Response
     */
    public function edit(PromoterDeviceAlternator $promoterDeviceAlternator)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromoterDeviceAlternator  $promoterDeviceAlternator
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromoterDeviceAlternator $promoterDeviceAlternator)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PromoterDeviceAlternator  $promoterDeviceAlternator
     * @return \Illuminate\Http\Response
     */
    public function destroy(PromoterDeviceAlternator $promoterDeviceAlternator)
    {
        //
    }
}
