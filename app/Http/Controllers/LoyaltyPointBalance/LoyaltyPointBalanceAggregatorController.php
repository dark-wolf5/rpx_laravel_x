<?php

namespace App\Http\Controllers\LoyaltyPointBalance;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPointBalanceAggregator;

class LoyaltyPointBalanceAggregatorController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param \App\Models\LoyaltyPointBalanceAggregator $LoyaltyPointBalance
     *
     * @return \Illuminate\Http\Response
     */
    public function show(LoyaltyPointBalanceAggregator $loyaltyPointBalance)
    {
        return $loyaltyPointBalance->show();
    }
}
