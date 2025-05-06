<?php

namespace App\Http\Controllers\LoyaltyPointLedger;

use App\Models\LoyaltyPointLedger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoyaltyPointLedgerController extends Controller
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
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\LoyaltyPointLedger $LoyaltyPointLedger
     *
     * @return \Illuminate\Http\Response
     */
    public function show(LoyaltyPointLedger $LoyaltyPointLedger)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\LoyaltyPointLedger $LoyaltyPointLedger
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(LoyaltyPointLedger $LoyaltyPointLedger)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request       $request
     * @param \App\Models\LoyaltyPointLedger $LoyaltyPointLedger
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoyaltyPointLedger $LoyaltyPointLedger)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\LoyaltyPointLedger $LoyaltyPointLedger
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoyaltyPointLedger $LoyaltyPointLedger)
    {
    }
}
