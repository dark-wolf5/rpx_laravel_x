<?php

namespace App\Http\Controllers\LoyaltyPointBalance;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPointBalance;
use App\Models\LoyaltyPointLedger;
use App\Models\PromoterBonus;
use Illuminate\Http\Request;

class LoyaltyPointBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, LoyaltyPointLedger $ledger)
    {
        return $ledger->index($request);
    }

    public function promoterLp(Request $request, PromoterBonus $promoterBonus) {
        return $promoterBonus->createForUser($request);
    }

    public function balanceList(Request $request, LoyaltyPointBalance $loyaltyPointBalance)
    {
        return $loyaltyPointBalance->balanceList($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(LoyaltyPointBalance $loyaltyPointBalance, Request $request)
    {
        return $loyaltyPointBalance->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\LoyaltyPointBalance $LoyaltyPointBalance
     *
     * @return \Illuminate\Http\Response
     */
    public function show(LoyaltyPointBalance $loyaltyPointBalance)
    {
        return $loyaltyPointBalance->show();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\LoyaltyPointBalance $LoyaltyPointBalance
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(LoyaltyPointBalance $LoyaltyPointBalance)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request        $request
     * @param \App\Models\LoyaltyPointBalance $LoyaltyPointBalance
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoyaltyPointBalance $LoyaltyPointBalance)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\LoyaltyPointBalance $LoyaltyPointBalance
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoyaltyPointBalance $LoyaltyPointBalance)
    {
    }

    public function reset(LoyaltyPointBalance $loyaltyPointBalance)
    {
        return $loyaltyPointBalance->reset();
    }

    public function setLpRate(Request $request, LoyaltyPointBalance $loyaltyPointBalance) {
        return $loyaltyPointBalance->setLpRate($request);
    }
}
