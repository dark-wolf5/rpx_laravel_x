<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use Auth;
use App\Models\LoyaltyTier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoyaltyTierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $tierList = $user->business->loyaltyTiers()->get();

        return response([
            'data' => $tierList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name'          => 'required|string|max:50|min:1',
            'description'   => 'required|string|max:360|min:15',
            'lp_entrance' => 'required|numeric|min:0',
        ]);

        $loyaltyTier = new LoyaltyTier();
        $loyaltyTier->uuid = Str::uuid();
        $loyaltyTier->business_id = $user->business->id;
        $loyaltyTier->name = $validatedData['name'];
        $loyaltyTier->description = $validatedData['description'];
        $loyaltyTier->lp_entrance = $validatedData['lp_entrance'];

        $loyaltyTier->save();

        return response([
            'tier' => $loyaltyTier,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\LoyaltyTier $loyaltyTier
     *
     * @return \Illuminate\Http\Response
     */
    public function show(LoyaltyTier $loyaltyTier)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\LoyaltyTier $loyaltyTier
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(LoyaltyTier $loyaltyTier)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\LoyaltyTier  $loyaltyTier
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoyaltyTier $loyaltyTier)
    {
        $validatedData = $request->validate([
            'name'          => 'required|string|max:50|min:1',
            'description'   => 'required|string|max:360|min:15',
            'lp_entrance' => 'required|numeric|min:0',
        ]);

        $loyaltyTier->name = $validatedData['name'];
        $loyaltyTier->description = $validatedData['description'];
        $loyaltyTier->lp_entrance = $validatedData['lp_entrance'];

        $loyaltyTier->save();

        return response([
            'tier' => $loyaltyTier,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\LoyaltyTier $loyaltyTier
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoyaltyTier $loyaltyTier)
    {
        $rewardListToUpdate = Reward::where('tier_id', $loyaltyTier->id)->get();

        foreach ($rewardListToUpdate as $reward) {
            $reward->tier_id = null;
            $reward->save();
        }

        $loyaltyTier->delete();

        return response([
            'tier' => $loyaltyTier,
        ]);
    }
}
